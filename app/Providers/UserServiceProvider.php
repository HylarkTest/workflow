<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Base;
use App\Models\User;
use App\Core\BaseType;
use App\Models\Action;
use App\Core\Groups\Role;
use Actions\Core\ActionRecorder;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        User::created(function (User $user) {
            /** @var \App\Models\Base $base */
            $base = Base::create([
                'name' => 'My base',
                'type' => BaseType::PERSONAL,
            ]);
            $user->bases()->attach($base, ['role' => Role::OWNER, 'use_account_avatar' => true]);
            /** @phpstan-ignore-next-line */
            $member = $user->bases()->find($base->id)->pivot;
            $base->recordAction($member, true);
            ActionRecorder::withPerformer($member, function () use ($base) {
                $base->run(function () use ($base) {
                    Action::withParent($base->createAction, function () use ($base) {
                        $base->spaces()->create(['name' => 'Personal']);
                    });
                });
            });
        });

        User::forceDeleted(function (User $user) {
            $bases = $user->ownedBases;
            $user->bases()->detach();
            $bases->each->delete();
        });
    }
}
