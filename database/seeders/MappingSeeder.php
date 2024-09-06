<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Space;
use App\Models\Mapping;
use Illuminate\Database\Seeder;
use Actions\Core\Contracts\ActionRecorder;

class MappingSeeder extends Seeder
{
    protected ActionRecorder $recorder;

    public function __construct(ActionRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalResolver = $this->recorder->getUserResolver();
        Space::query()
            ->whereHasMorph('owner', '*', fn ($q) => $q->where('id', '>', 100))
            ->with('owner')
            ->each(function (Space $space) {
                $creator = $space->owner;
                $this->recorder->setUserResolver(fn () => $creator);
                Mapping::factory(random_int(1, 5))
                    ->for($space)
                    ->for($space->owner)
                    ->create();
            });

        $this->recorder->setUserResolver($originalResolver);
    }
}
