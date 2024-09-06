<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Support\SupportFolder;
use Illuminate\Database\Eloquent\Model;
use App\Policies\Concerns\HandlesNovaAuthorization;

/**
 * @phpstan-type SupportModel \App\Models\Support\SupportArticle|\App\Models\Support\SupportCategory|\App\Models\Support\SupportFolder
 *
 * @method bool viewAny(\App\Models\User $user)
 * @method bool view(\App\Models\User $user, SupportModel $model)
 * @method bool create(\App\Models\User $user)
 * @method bool update(\App\Models\User $user, SupportModel $model)
 * @method bool delete(\App\Models\User $user, SupportModel $model)
 * @method bool addCategory(\App\Models\User $user, SupportModel $model)
 */
class SupportArticlePolicy
{
    use HandlesNovaAuthorization;

    public function addsupportarticle(User $user, Model $model): bool
    {
        if ($model instanceof SupportFolder) {
            return $this->hasEditorPermissions($user);
        }

        return false;
    }

    public function __call(string $name, array $arguments): bool
    {
        if (! \in_array($name, [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'addCategory',
        ], true)) {
            throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
        }

        // If the user is connected to the resources database as the dev user
        // then they should not be able to edit anything
        if (
            ! \in_array($name, ['viewAny', 'view'], true)
            && config('hylark.support.database') === 'resources'
            && ! can_edit_resources_database()
        ) {
            return false;
        }

        return $this->hasEditorPermissions($arguments[0]);
    }
}
