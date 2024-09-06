<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Core\Clamav;
use App\Models\Item;
use MarkupUtils\Delta;
use MarkupUtils\TipTap;
use App\Models\Emailable;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use LighthouseHelpers\Utils;
use App\Models\EmailAddressable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Scope;
use App\Exceptions\ClamavException;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use AccountIntegrations\Core\Emails\Email;
use App\GraphQL\ExternalAssociationBatchLoader;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\AddsAssociations;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use NunoMaduro\Collision\Exceptions\ShouldNotHappen;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;

/**
 * @phpstan-type GroupedEmailsQueryArgs = array{
 *     first: int,
 *     after?: string,
 *     forNode?: string,
 *     search?: string,
 *     group: string,
 *     includeGroups?: string[],
 * }
 * @phpstan-type EmailsQueryArgs = array{
 *     first: int,
 *     after?: string,
 *     sourceId: string|\AccountIntegrations\Models\IntegrationAccount,
 *     mailboxId?: string,
 *     forNode?: string,
 *     search?: string,
 * }
 * @phpstan-type AttachmentInput = array{
 *     file?: \Illuminate\Http\UploadedFile|null,
 *     link?: string|null,
 *     isInline: boolean,
 *     contentId?: string|null,
 *     name?: string,
 * }
 * @phpstan-type CreateEmailInput = array{
 *     sourceId: int,
 *     to?: string[]|null,
 *     cc?: string[]|null,
 *     bcc?: string[]|null,
 *     subject: string,
 *     html?: string|null,
 *     delta?: array,
 *     tiptap?: array,
 *     attachments?: array<AttachmentInput>,
 *     fromDraft?: string|null,
 *     isDraft: boolean,
 *     associations?: int[],
 * }
 */
class EmailQuery extends Mutation
{
    use AddsAssociations;

    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\AccountIntegrations\Core\Emails\Email>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    use PaginatesQueries;

    /**
     * @param  GroupedEmailsQueryArgs  $args
     *
     * @throws \Exception
     */
    public function indexGrouped(?Item $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        // Right now users can only group by the integration account
        $this->validate($args, [
            'group' => 'required|in:source',
        ], $resolveInfo);

        $groups = $context->user()->integrationAccounts;
        if ($args['includeGroups'] ?? false) {
            $groups = $groups->whereIn('id', $args['includeGroups']);
        }

        return [
            'groups' => $groups->map(function (IntegrationAccount $source) use ($rootValue, $args, $context, $resolveInfo) {
                return [
                    'groupHeader' => $source->account_name,
                    'group' => $source,
                    ...$this->index(
                        $rootValue,
                        [
                            ...$args,
                            'sourceId' => $source,
                        ],
                        $context,
                        $resolveInfo
                    ),
                ];
            }),
        ];
    }

    /**
     * @param  EmailsQueryArgs  $args
     *
     * @throws \Exception
     */
    public function index(?Item $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $node = $rootValue ?? $args['forNode'] ?? null;
        if (is_string($node)) {
            $node = Utils::resolveModelFromGlobalId($node);
            /** @var \App\Models\Item $node */
        }

        $perPage = $args['first'];

        $source = $this->getSource($context, $args);

        $offset = isset($args['after']) ? (int) base64_decode($args['after'], true) + 1 : 0;

        $options = [
            'first' => $perPage + 1,
            'offset' => $offset,
            'search' => $args['search'] ?? null,
        ];

        if ($node) {
            $nodeOptions = $node->getEmailFilterOptions($source);
            if (! array_filter($nodeOptions)) {
                return [
                    'edges' => [],
                    'pageInfo' => [
                        'hasNextPage' => false,
                        'hasPreviousPage' => false,
                        'startCursor' => null,
                        'endCursor' => null,
                    ],
                ];
            }
            $options = [
                ...$options,
                ...$node->getEmailFilterOptions($source),
            ];
        }

        $emails = $source->getEmails($args['mailboxId'] ?? null, $options);

        $startCursor = null;
        $endCursor = null;

        return [
            'edges' => $emails->take($perPage)->map(function (Email $email, $index) use ($options, &$startCursor, &$endCursor) {
                $cursor = base64_encode((string) ($options['offset'] + $index));
                if (! $startCursor) {
                    $startCursor = $cursor;
                }
                $endCursor = $cursor;

                return [
                    'cursor' => $cursor,
                    'node' => $email,
                ];
            })->toArray(),
            'pageInfo' => [
                'hasNextPage' => $emails->count() > $perPage,
                'hasPreviousPage' => $offset > 0,
                'startCursor' => $startCursor,
                'endCursor' => $endCursor,
            ],
        ];
    }

    /**
     * @param  null  $rootValue
     * @param  array{ emailId: string, mailboxId: string, sourceId: string }  $args
     *
     * @throws \Exception
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Email
    {
        return $this->getSource($context, $args)->getEmail($args['emailId'], $args['mailboxId']);
    }

    protected function getFileFromAttachmentLink(string $link): UploadedFile
    {
        if (Str::startsWith($link, config('app.url').'/attachment/')) {
            $request = Request::create($link, 'GET');
            $route = app('router')->getRoutes()->match($request);
            /** @var \Symfony\Component\HttpFoundation\StreamedResponse $streamedResponse */
            $streamedResponse = $route->run();
            ob_start();
            $streamedResponse->sendContent();
            $contents = ob_get_clean();
        } else {
            $contents = file_get_contents($link);
        }
        $info = pathinfo($link);
        $path = '/tmp/'.$info['basename'];
        file_put_contents($path, $contents);
        $uploadedFile = new UploadedFile($path, $info['basename']);
        try {
            resolve(Clamav::class)->check($uploadedFile);
        } catch (ClamavException $e) {
            unlink($path);
            throw $e;
        }

        return $uploadedFile;
    }

    /**
     * @param  null  $rootValue
     * @param  array{ input: CreateEmailInput }  $args
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'to',
            'cc',
            'bcc',
            'subject',
            'html',
            'delta',
            'tiptap',
        ]);

        $source = $this->getSource($context, $args);
        $data['html'] = '<p></p>';
        if (! empty($data['delta'])) {
            $data['html'] = (string) (new Delta($data['delta']))->convertToHTML();
            unset($data['delta'], $data['tiptap']);
        } elseif (! empty($data['tiptap'])) {
            $data['html'] = (string) (new TipTap($data['tiptap']))->convertToHTML();
            unset($data['delta'], $data['tiptap']);
        }

        $data['from'] = $source->account_name;
        $attachments = $args['input']['attachments'] ?? [];
        foreach ($attachments as $index => $attachment) {
            if (! ($attachment['link'] ?? null)) {
                continue;
            }
            $attachments[$index]['file'] = $this->getFileFromAttachmentLink($attachment['link']);
        }

        $items = $this->getAssociatedItems($context->base(), $args, MappingFeatureType::EMAILS);

        $fromDraft = $args['input']['fromDraft'] ?? false;

        if ($args['input']['isDraft']) {
            if ($fromDraft) {
                $data['id'] = $fromDraft;
            }
            $email = $source->saveDraft($data, $attachments);
        } else {
            if ($fromDraft) {
                $source->deleteDraft($fromDraft);
            }
            $email = $source->createEmail($data, $attachments);
        }

        foreach ($items as $item) {
            $item->emailables()->updateOrCreate([
                'email_id' => $email->id,
                'integration_account_id' => $source->id,
                'email_created_at' => now(),
            ]);
        }

        return $this->mutationResponse(200, 'Email was sent successfully', [
            'email' => $email,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'id',
            'isFlagged',
            'isSeen',
            'priority',
        ]);

        $source = $this->getSource($context, $args);

        $email = $source->updateEmail($args['input']['mailboxId'], $data);

        return $this->mutationResponse(200, 'Email was updated successfully', [
            'email' => $email,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $source->deleteEmail($args['input']['mailboxId'], $args['input']['id']);

        Emailable::query()->where([
            'integration_account_id' => $source->id,
            'email_id' => $args['input']['id'],
        ])->delete();

        return $this->mutationResponse(200, 'Email was deleted successfully');
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function associate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $email = $source->getEmail($args['input']['id'], $args['input']['mailboxId']);

        if (! $email->mailbox) {
            throw new ShouldNotHappen;
        }

        $existing = $node->emailables()->whereIn('email_id', $email->allIds())->where([
            'integration_account_id' => $source->id,
        ])->first();

        if ($existing) {
            $existing->update(['email_id' => $email->internetMessageId]);
        } else {
            $node->emailables()->create([
                'mailbox_id' => $email->mailbox->id,
                'email_id' => $email->internetMessageId,
                'integration_account_id' => $source->id,
                'email_created_at' => $email->createdAt,
            ]);
        }

        return $this->mutationResponse(200, 'Email was associated successfully', [
            'email' => $email,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function dissociate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $email = $source->getEmail($args['input']['id'], $args['input']['mailboxId']);

        if (! $email->mailbox) {
            throw new ShouldNotHappen;
        }

        $node->emailables()->where([
            'mailbox_id' => $email->mailbox->id,
            'email_id' => $email->internetMessageId,
            'integration_account_id' => $source->id,
        ])->delete();

        return $this->mutationResponse(200, 'Email was dissociated successfully', [
            'email' => $email,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function associateEmailAddress($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        if (! $context->base()->accountLimits()->canAssociateEmailAddresses($node)) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $source = $this->getSource($context, $args);

        $address = $args['input']['address'];

        $node->emailAddressables()->updateOrCreate([
            'address' => $address,
            'integration_account_id' => $source->id,
        ]);

        return $this->mutationResponse(200, 'Email address was associated successfully');
    }

    /**
     * @param  null  $root
     * @param  array{ input: array{ nodeId: string, address: string, sourceId: int } }  $args
     *
     * @throws \Exception
     */
    public function dissociateEmailAddress($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $address = $args['input']['address'];

        $node->emailAddressables()->where([
            'address' => $address,
            'integration_account_id' => $source->id,
        ])->delete();

        return $this->mutationResponse(200, 'Email address was dissociated successfully');
    }

    public function resolveAssociations(Email $email, array $args, AppContext $context, ResolveInfo $resolveInfo): ?SyncPromise
    {
        $ids = $email->allIds();

        return $ids ? ExternalAssociationBatchLoader::instanceFromExternal(
            Item::class, Emailable::class, $email->account->id
        )->loadAndResolve($ids) : null;
    }

    /**
     * @param  null  $root
     * @param  array{first: int, after?: string, addresses?: string[]}  $args
     *
     * @throws \JsonException
     */
    public function resolveEmailAddressAssociations($root, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $query = EmailAddressable::query()->has('emailAddressable');

        if (isset($args['addresses'])) {
            $query->whereIn('address', $args['addresses']);
        }

        return $this->paginateQuery($query, $args);
    }

    /**
     * @param  array{ sourceId?: int|string|\AccountIntegrations\Models\IntegrationAccount, input?: array{ sourceId: int|string|\AccountIntegrations\Models\IntegrationAccount } }  $args
     */
    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::EMAILS);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>  $emails
     */
    protected function buildEmailsConnection(Collection $emails, bool $hasMorPages, string $nextPage, int $perPage): array
    {
        return [
            'data' => $emails,
            'paginatorInfo' => [
                'nextPageToken' => $nextPage,
                'hasMorePages' => $hasMorPages,
                'perPage' => $perPage,
            ],
        ];
    }
}
