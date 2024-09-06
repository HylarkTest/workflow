<?php

declare(strict_types=1);

namespace App\Core\Bootstrap;

use App\Models\Base;
use App\Models\User;
use App\Core\BaseType;
use App\Models\Action;
use App\Core\Groups\Role;
use App\Core\BaseRepository;
use Actions\Core\ActionRecorder;
use Illuminate\Support\Facades\DB;

class Bootstrapper
{
    public function bootstrap(User $user, array $bootstrapData): void
    {
        DB::transaction(function () use ($user, $bootstrapData) {
            foreach ($bootstrapData as $baseData) {
                $baseType = $baseData['baseType'] ?? BaseType::PERSONAL->value;
                $baseName = $baseData['name'] ?? null;

                if ($baseType === BaseType::PERSONAL->value) {
                    $base = $user->firstPersonalBase();
                    $base->update(['name' => $baseName]);
                    $member = $base->pivot;
                } else { /* The base is collaborative */
                    $personalBase = $user->firstPersonalBase();
                    ActionRecorder::withPerformer(
                        $personalBase->pivot,
                        fn () => Action::withParent(
                            $personalBase->createAction,
                            fn () => $personalBase->createDefaultEntries()
                        )
                    );

                    $base = Base::create([
                        'name' => $baseName,
                        'type' => $baseType,
                    ]);
                    $user->bases()->attach($base, ['role' => Role::OWNER]);
                    /** @phpstan-ignore-next-line */
                    $member = $user->bases()->find($base->id)->pivot;

                    $base->run(function () use ($base, $member) {
                        ActionRecorder::withPerformer(
                            $member,
                            fn () => Action::withParent($base->createAction, function () use ($base) {
                                $base->run(
                                    function () use ($base) {
                                        $base->spaces()->create(['name' => 'Main']);
                                    }
                                );
                            })
                        );
                    });
                }
                $base->run(function () use ($base, $member, $baseData) {
                    ActionRecorder::withPerformer(
                        $member,
                        fn () => Action::withParent($base->createAction, function () use ($base, $baseData) {
                            $base->run(
                                function () use ($base, $baseData) {
                                    (new BaseRepository)->bootstrapBase($base, $baseData);
                                }
                            );
                        })
                    );
                });
            }
        }, 3);
    }
}
