<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use AccountIntegrations\Core\Scope;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Models\IntegrationAccount;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;

class MailboxQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\AccountIntegrations\Core\Emails\Mailbox>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $perPage = $args['first'] ?? 100;

        $source = $this->getSource($context, $args);

        $mailboxes = $source->getMailboxes();

        $count = $mailboxes->count();
        $currentPage = $args['page'] ?? 1;
        $firstItem = $count > 0 ? ($currentPage - 1) * $perPage + 1 : null;
        $lastItem = $count > 0 ? $firstItem + $count - 1 : null;

        return [
            'data' => $mailboxes,
            'paginatorInfo' => [
                'count' => $count,
                'currentPage' => $currentPage,
                'firstItem' => $firstItem,
                'lastItem' => $lastItem,
                'perPage' => $perPage,
            ],
        ];
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $source = $this->getSource($context, $args);

        $mailbox = $source->createMailbox($data['name']);

        return $this->mutationResponse(200, 'Mailbox was created successfully', [
            'mailbox' => $mailbox,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $mailbox = $source->updateMailbox($args['input']['id'], $args['input']['name']);

        return $this->mutationResponse(200, 'Mailbox was updated successfully', [
            'mailbox' => $mailbox,
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

        $source->deleteTodoList($args['input']['id']);

        return $this->mutationResponse(200, 'Mailbox was deleted successfully');
    }

    /**
     * @param  array{ forNode?: string }  $args
     */
    public function resolveUnseenCount(Mailbox $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): int
    {
        if ($args['forNode'] ?? null) {
            if ($rootValue->unseenCount === 0) {
                return 0;
            }
            /** @var \App\Models\Item $node */
            $node = Utils::resolveModelFromGlobalId($args['forNode']);
            $source = $rootValue->account;

            $options = $node->getEmailFilterOptions($source);
            if (! array_filter($options)) {
                return 0;
            }
            $options = [...$options, 'unread' => true];

            return $source->emailRepository()->getEmailsCount($rootValue->id, $options);
        }

        return $rootValue->unseenCount;
    }

    /**
     * @param  array{ forNode?: string }  $args
     */
    public function resolveTotal(Mailbox $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): int
    {
        if ($args['forNode'] ?? null) {
            if ($rootValue->total === 0) {
                return 0;
            }
            /** @var \App\Models\Item $node */
            $node = Utils::resolveModelFromGlobalId($args['forNode']);
            $source = $rootValue->account;

            $options = $node->getEmailFilterOptions($source);
            if (! array_filter($options)) {
                return 0;
            }

            return $source->emailRepository()->getEmailsCount($rootValue->id, $options);
        }

        return $rootValue->total;
    }

    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::EMAILS);
    }
}
