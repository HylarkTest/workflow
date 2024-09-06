<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Base;
use App\Models\User;
use App\Models\Action;
use App\Core\Groups\Role;
use App\GraphQL\AppContext;
use App\Models\BaseUserPivot;
use App\Core\MemberActionType;
use Illuminate\Support\Carbon;
use App\Events\Auth\MemberLeft;
use Actions\Core\ActionRecorder;
use App\Events\Auth\OwnerRemoved;
use LighthouseHelpers\Core\Mutation;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\Core\Preferences\BasePreferences;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use App\Core\Preferences\BaseUserPreferences;
use Stancl\Tenancy\Database\TenantCollection;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use LighthouseHelpers\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class BaseQuery extends Mutation
{
    /**
     * @param  null  $root
     */
    public function index($root, array $args, AppContext $context): TenantCollection
    {
        return $context->user()->bases()->get();
    }

    public function show(): Base
    {
        /** @var \App\Models\Base|null $base */
        $base = tenant();

        abort_if(! $base, 404);

        return $base;
    }

    /**
     * @param  null  $root
     */
    public function update($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Base|null $base */
        $base = tenant();

        abort_if(! $base, 404);

        $data = $args['input'];

        if ($name = $data['name'] ?? null) {
            if ($base->is($context->user()->firstPersonalBase())) {
                throw ValidationException::withMessages(['input.name' => ['You cannot update the name of your personal base.']]);
            }
            $base->name = $name;
        }

        if ($description = $data['description'] ?? null) {
            $base->description = $description;
        }

        if (\array_key_exists('image', $data)) {
            $base->updateImage($data['image'], 'image', 'base-images');
        }

        if (\array_key_exists('accentColor', $data)) {
            $base->settings->updatePreferences(fn (BasePreferences $preferences) => $preferences->accentColor = $data['accentColor']);
        }

        if (\array_key_exists('homepage', $data)) {
            $homepage = $this->validate(
                $data,
                [
                    'homepage' => 'array',
                    'homepage.spaces' => ['array', 'max:'.$base->spaces()->count()],
                    'homepage.spaces.*' => 'array',
                    'homepage.spaces.*.pages' => ['nullable', 'array', 'max:50'],
                ],
                $resolveInfo
            )['homepage'];
            $base->settings->updatePreferences(fn (BasePreferences $preferences) => $preferences->homepage = $homepage);
        }

        $base->save();

        $response = $this->baseMutationResponse($base);
        Subscription::broadcast('baseUpdated', $response);

        return $response;
    }

    /**
     * @param  null  $root
     */
    public function destroy($root, array $args, AppContext $context): array
    {
        /** @var \App\Models\Base $base */
        $base = tenant();
        /** @var \App\Models\User $user */
        $user = $context->user();

        ActionRecorder::withoutRecording(fn () => $base->delete());
        $personalBase = $user->firstPersonalBase();
        $user->setActiveBase($personalBase);
        tenancy()->initialize($personalBase);

        $response = $this->baseMutationResponse(null);
        Subscription::broadcast('baseDeleted', [
            'base' => ['id' => $base->global_id],
            'activeBase' => $personalBase,
        ]);

        return $response;
    }

    public function leave(null $root, array $args, AppContext $context): array
    {
        /** @var \App\Models\Base $base */
        $base = tenant();
        /** @var \App\Models\User $user */
        $user = $context->user();

        $base->members()->detach($user->id);

        $personalBase = $user->firstPersonalBase();
        $user->setActiveBase($personalBase);
        tenancy()->initialize($personalBase);

        event(new MemberLeft($user, $base));

        return $this->baseMutationResponse(null);
    }

    public function resolveMembers(Base $base, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'members';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(new SimpleModelsLoader('members', fn (BelongsToMany $query) => $query->orderBy('id'))),
        )->load($base)->then(function (Collection $users) use ($base) {
            /** @phpstan-ignore-next-line Pivot definitely exists */
            return $users->map(function (User $user) use ($base) {
                /** @var \App\Models\BaseUserPivot $member */
                $member = $user->pivot;
                $member->setRelation('user', $user->withoutRelations());
                $member->pivotParent = $base;

                return $member;
            });
        });
    }

    public function resolveInvites(Base $base, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'invites';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(new SimpleModelsLoader(
                'memberInvites',
                /** @phpstan-ignore-next-line */
                fn (HasMany $builder) => $builder
                    /** @phpstan-ignore-next-line */
                    ->when($args['status'] ?? null, function (Builder $builder, string $status) {
                        return match ($status) {
                            /** @phpstan-ignore-next-line */
                            'PENDING' => $builder->pending(),
                            /** @phpstan-ignore-next-line */
                            'ACCEPTED' => $builder->accepted(),
                            default => null,
                        };
                    })
                    ->latest(),
            )),
        )->load($base)->then(fn ($invites) => $invites->unique('email'));
    }

    public function resolveIsAuthenticatedUser(BaseUserPivot $root, array $args, AppContext $context): bool
    {
        return (bool) $root->user->is($context->user());
    }

    public function updateMember(null $root, array $args, AppContext $context): array
    {
        $base = $context->base();
        $data = $args['input'];

        $id = $data['id'];
        [$type, $id] = resolve(GlobalId::class)->decode($id);
        abort_if($type !== 'Member', 404);

        $member = $base->members()->wherePivot('id', $id)->firstOrFail();

        $role = Role::from($data['role']);

        /** @phpstan-ignore-next-line */
        $oldRole = $member->pivot->role;

        if ($role === Role::OWNER || $oldRole === Role::OWNER) {
            $this->authorize('addOwner', $base);
        }

        /** @var \App\Models\BaseUserPivot $pivot */
        $pivot = $member->pivot;
        $pivot->role = $role;
        $pivot->save();
        if ($oldRole === Role::OWNER && $pivot->wasChanged('role')) {
            event(new OwnerRemoved($member, $base));
        }

        Subscription::broadcast('memberUpdated', $base, true);

        return $this->mutationResponse(200, '', [
            'base' => $member->bases()->find($base->id), // Need the pivot to allow querying on the member data
        ]);
    }

    public function deleteMember(null $root, array $args, AppContext $context): array
    {
        $base = $context->base();
        $data = $args['input'];

        $id = $data['id'];
        [$type, $id] = resolve(GlobalId::class)->decode($id);
        abort_if($type !== 'Member', 404);

        $member = $base->members()->wherePivot('id', $id)->firstOrFail();

        if ($member->pivot?->role->isOwner()) {
            $this->authorize('addOwner', $base);
            if ($base->members()->wherePivot('role', Role::OWNER)->count() === 1) {
                throw ValidationException::withMessages(['input.id' => ['You cannot remove the last owner of a base.']]);
            }
        }

        /** @var \App\Models\BaseUserPivot $pivot */
        $pivot = $member->pivot;
        $pivot->delete();

        Action::createAction(
            $member,
            $context->user(),
            MemberActionType::MEMBER_REMOVED(),
        );

        event(new MemberLeft($member, $base));

        return $this->mutationResponse(200, '', [
            'base' => $base->fresh(),
        ]);
    }

    public function updateProfile(null $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();
        /** @var \App\Models\BaseUserPivot $pivot */
        $pivot = $base->pivot;

        $data = $args['input'];

        if (\array_key_exists('displayName', $data)) {
            $pivot->name = $data['displayName'];
        }

        if (isset($data['useAccountAvatar'])) {
            $pivot->use_account_avatar = $data['useAccountAvatar'];
        }

        if (\array_key_exists('displayAvatar', $data)) {
            $pivot->updateImage($data['displayAvatar'], 'avatar', 'avatars');
        }

        if (\array_key_exists('preferences', $data)) {
            $pivot->updatePreferences(function (BaseUserPreferences $preferences) use ($data, $resolveInfo, $base) {
                if (\array_key_exists('shortcuts', $data['preferences']) && $data['preferences']['shortcuts'] !== $preferences->shortcuts) {
                    $preferences->shortcuts = $data['preferences']['shortcuts'];
                }
                if (\array_key_exists('widgets', $data['preferences'])) {
                    $preferences->setWidgets($data['preferences']['widgets']);
                }
                if (\array_key_exists('homepage', $data['preferences'])) {
                    $homepage = $this->validate(
                        $data,
                        [
                            'preferences.homepage' => 'array',
                            'preferences.homepage.shortcuts' => 'array',
                            'preferences.homepage.shortcuts.customize' => 'in:FULL,SMALL,HIDE',
                            'preferences.homepage.shortcuts.integrations' => 'in:FULL,SMALL,HIDE',
                            'preferences.homepage.spaces' => ['array', 'max:'.$base->spaces()->count()],
                            'preferences.homepage.spaces.*' => 'array',
                            'preferences.homepage.spaces.*.pages' => ['nullable'],
                        ],
                        $resolveInfo
                    )['preferences']['homepage'];
                    /** @phpstan-ignore-next-line Should be fixed in the update bag */
                    $preferences->homepage = array_merge(
                        $preferences->homepage,
                        $homepage,
                    );
                }
            });
        }

        $pivot->save();

        Subscription::broadcast('memberUpdated', $base, true);

        return $this->mutationResponse(200, 'User was updated successfully', [
            'base' => $base,
            'activeBase' => $base,
        ]);
    }

    public function resolveAuthMemberGlobalId(Base $base, array $args, AppContext $context): string
    {
        return $this->resolveAuthMember($base, $context)->global_id;
    }

    public function resolveAuthMemberRole(Base $base, array $args, AppContext $context): string
    {
        return $this->resolveAuthMember($base, $context)->role->value;
    }

    public function resolveAuthMemberDisplayName(Base $base, array $args, AppContext $context): ?string
    {
        return $this->resolveAuthMember($base, $context)->name;
    }

    public function resolveAuthMemberDisplayAvatar(Base $base, array $args, AppContext $context): ?string
    {
        return $this->resolveAuthMember($base, $context)->avatarUrl;
    }

    public function resolveAuthMemberUseAccountAvatar(Base $base, array $args, AppContext $context): bool
    {
        return (bool) $this->resolveAuthMember($base, $context)->use_account_avatar;
    }

    public function resolveAuthMemberAddedAt(Base $base, array $args, AppContext $context): Carbon
    {
        return $this->resolveAuthMember($base, $context)->created_at;
    }

    public function resolveAuthMemberSettings(Base $base, array $args, AppContext $context): ?array
    {
        return $this->resolveAuthMember($base, $context)->settingsArray;
    }

    protected function resolveAuthMember(Base $base, AppContext $context): BaseUserPivot
    {
        /** @var ?BaseUserPivot $pivot */
        $pivot = $base->pivot;
        if ($pivot?->user_id === $context->user()->id) {
            return $base->pivot;
        }
        $pivot = $context->user()->bases()->findOrFail($base->id)->pivot;
        $base->setRelation('pivot', $pivot);

        return $pivot;
    }

    protected function baseMutationResponse(?Base $base): array
    {
        return $this->mutationResponse(200, '', [
            'base' => $base,
            'activeBase' => tenant(),
        ]);
    }
}
