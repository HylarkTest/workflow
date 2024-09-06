<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\TodoList;
use Illuminate\Database\Seeder;
use Actions\Core\Contracts\ActionRecorder;

class TodoSeeder extends Seeder
{
    protected ActionRecorder $recorder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalResolver = $this->recorder->getUserResolver();

        User::query()->where('id', '>', 100)->each(function (User $user) {
            $this->recorder->setUserResolver(fn () => $user);
            TodoList::factory(random_int(1, 2))
                ->withTodos()
                ->for($user, 'owner')
                ->create();
        });

        $this->recorder->setUserResolver($originalResolver);
    }
}
