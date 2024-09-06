<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use App\Models\Base;
use App\Models\User;
use App\Core\BaseType;
use App\Core\Groups\Role;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:base';

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    protected \Illuminate\Database\Eloquent\Collection $baselessUsers;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Every user should have and own at least one base.';

    protected function check(OutputInterface $output): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $baselessUsers */
        $baselessUsers = User::query()->whereDoesntHave('bases', function ($query) {
            $query->where('role', Role::OWNER);
        })->get(['id', 'name', 'created_at', 'updated_at']);

        $this->baselessUsers = $baselessUsers;

        if ($this->baselessUsers->isNotEmpty()) {
            $message = $this->baselessUsers->count().' users were found without a base.';

            $this->error($message);
            $this->table(['id', 'name', 'created_at', 'updated_at'], $this->baselessUsers);

            $this->report($message);
        }

        if ($this->baselessUsers->isEmpty()) {
            $this->info('All users have bases');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return $this->baselessUsers->count();
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if ($this->baselessUsers->isNotEmpty()) {
            if (! $confirmFixes || $this->confirm('Would you like to create bases for users?')) {
                $this->baselessUsers
                    ->each(function (User $user) {
                        tap(Base::query()->create([
                            'name' => $user->name,
                            'type' => BaseType::PERSONAL,
                        ]), static function (Base $base) use ($user) {
                            $user->bases()->attach($base, ['role' => Role::OWNER]);
                        });
                    });
            }
        } else {
            $this->info('No fixes required for the bases table.');
        }

        return 0;
    }
}
