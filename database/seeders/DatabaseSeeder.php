<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\DB\Health\GroupedInviteCheckCommand;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function run(): void
    {
        $filesystem = Storage::disk(config('mappings.disk'));
        foreach ($filesystem->directories() as $directory) {
            $filesystem->deleteDirectory($directory);
        }

        $this->container->make(Factory::class)->load(base_path('api/tests/Factories'));
        $this->call(DemoSeeder::class);

        $this->call(UserSeeder::class);

        // $this->call(TodoSeeder::class);
        $this->call(MarkerSeeder::class);
        $this->call(SpaceSeeder::class);
        $this->call(MappingSeeder::class);
        Invite::withoutEvents(fn () => $this->call(InviteSeeder::class));

        $this->command->callSilent(GroupedInviteCheckCommand::class, ['--reset']);
    }
}
