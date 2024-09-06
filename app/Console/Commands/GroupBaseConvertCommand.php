<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Base;
use App\Models\User;
use App\Core\BaseType;
use App\Models\Action;
use App\Core\Groups\Role;
use Illuminate\Console\Command;
use Actions\Core\ActionRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GroupBaseConvertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'group-base:convert {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a personal base to a collaborative base.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user');

        /** @var \App\Models\User $user */
        $user = User::find($userId);
        $name = $user->name;

        $bases = $user->bases;

        $collaborativeBase = $bases->firstWhere('type', BaseType::COLLABORATIVE);
        if ($collaborativeBase) {
            $answer = $this->confirm("User {$name} already has a collaborative base. Do you want to replace this with their personal base?");

            if (! $answer) {
                $this->info("Base for user {$name} not converted.");

                return 1;
            }
            $collaborativeBase->delete();
        }

        $this->info("Converting personal base for user {$name} into collaborative...");

        $baseToConvert = $user->firstPersonalBase();
        ActionRecorder::withoutRecording(function () use ($baseToConvert) {
            Model::withoutTimestamps(function () use ($baseToConvert) {
                $baseToConvert->type = BaseType::COLLABORATIVE;
                $baseToConvert->name = 'Collaborative Base';
                $baseToConvert->save();
            });
        });

        /** @var \App\Models\Base $base */
        $base = Base::create([
            'name' => 'My base',
            'type' => BaseType::PERSONAL,
        ]);
        $user->bases()->attach($base, ['role' => Role::OWNER]);
        Action::withParent($base->createAction, function () use ($base) {
            $base->run(fn () => $base->spaces()->create(['name' => 'Personal']));
        });

        // We need to move the user's avatar to their new personal base, if
        // they have one.
        if ($user->avatar) {
            $oldBaseStorage = $baseToConvert->run(fn () => Storage::disk('images'));
            $newBaseStorage = $base->run(fn () => Storage::disk('images'));

            $oldAvatar = $oldBaseStorage->get($user->avatar);

            if ($oldAvatar) {
                $newBaseStorage->put($user->avatar, $oldAvatar);
                $oldBaseStorage->delete($user->avatar);
            }
        }

        $this->info("Base for user {$name} converted.");

        return 0;
    }
}
