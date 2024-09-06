<?php

declare(strict_types=1);

namespace App\Console\Commands\DB;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UserRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:restore {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a soft deleted user.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()
            ->withTrashed();

        $id = $this->argument('id');

        if (! \is_string($id)) {
            $this->error('The id must be a string.');

            return 1;
        }

        if (Str::contains($id, '@')) {
            $query->where('email', 'like', '%'.$id);
        } else {
            $query->where('id', $id);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('No users found.');

            return 1;
        }

        if ($users->count() > 1) {
            return $this->handleMultipleUsers($users);
        }

        /** @var \App\Models\User $user */
        $user = $users->first();

        if (! $user->trashed()) {
            $this->error('User is not soft deleted.');

            return 1;
        }

        $user->restore();

        $this->info('User restored.');

        return 0;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>  $users
     */
    protected function handleMultipleUsers(Collection $users): int
    {
        [$deletedUsers, $nonDeletedUsers] = $users->partition(fn (User $user) => $user->trashed());

        if ($nonDeletedUsers->isNotEmpty()) {
            $this->error('There is already an active user with that email.');
            $this->table(['id', 'name', 'email'], $nonDeletedUsers->map(fn (User $user) => $user->only(['id', 'name', 'email'])));

            return 1;
        }

        $this->info('Multiple deleted users found with that email.');
        $this->table(['No.', 'id', 'name', 'email', 'deleted_at'], $users->map(fn (User $user, int $index) => [
            $index + 1,
            $user->id,
            $user->name,
            Str::after($user->email, (string) $user->deleted_at),
            $user->deleted_at,
        ]));

        $index = $this->ask('Which user do you want to restore?') - 1;

        $user = $users->get($index);

        if (! $user) {
            $this->error('Invalid number, please select from the table.');

            return 1;
        }

        $user->restore();

        return 0;
    }
}
