<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Models\User;
use App\Models\Space;
use App\Models\Mapping;
use Mappings\Models\Item;
use Illuminate\Support\Str;
use App\Models\Contracts\Owner;

trait SeedsJsonFiles
{
    protected \Faker\Generator $faker;

    public function __construct(\Faker\Generator $faker)
    {
        $this->faker = $faker;
    }

    protected function seedDirectory(string $dir, Owner $owner, ?Space $space = null): void
    {
        collect(scandir($dir))
            ->filter(fn (string $file): string => $file[0] !== '.')->values()
            ->each(function ($file, $key) use ($owner, $dir, $space) {
                $dir .= '/'.$file;
                if (is_dir($dir)) {
                    if ($key === 0 && $owner instanceof User) {
                        $space = tap($owner->spaces->first())->update(['name' => $file]);
                    } else {
                        $space = $owner->spaces()->create(['name' => $file]);
                    }

                    $this->seedDirectory($dir, $owner, $space);

                    return;
                }

                $json = json_decode(file_get_contents($dir), true);

                $mapping = factory(Mapping::class)->make($json['mapping']);
                createMappingWithAction($mapping, $owner, $space);

                $itemAttributes = $json['items'];

                if (! $itemAttributes) {
                    return;
                }

                if (isset($itemAttributes[0])) {
                    foreach ($itemAttributes as $itemAttribute) {
                        Item::query()->forceCreate(array_merge([
                            'mapping_id' => $mapping->getKey(),
                        ], $this->fakeAttributes($itemAttribute)));
                    }
                } else {
                    for ($i = 1; $i <= $this->faker->numberBetween(5, 20); $i++) {
                        Item::query()->forceCreate(array_merge([
                            'mapping_id' => $mapping->getKey(),
                        ], $this->fakeAttributes($itemAttributes)));
                    }
                }
            });
    }

    protected function fakeAttributes($attributes): array
    {
        $argValues = [
            'gender' => $this->faker->gender,
        ];

        return collect($attributes)
            ->mapWithKeys(function ($value, $key) use ($argValues) {
                if (\is_array($value)) {
                    $value = $this->fakeAttributes($value);
                } elseif (\is_string($value) && $value[0] === ':') {
                    if (Str::contains($value, '|')) {
                        [$method, $args] = explode('|', mb_substr($value, 1), 2);
                    } else {
                        $method = mb_substr($value, 1);
                        $args = '';
                    }

                    $args = explode(',', $args);

                    $args = array_map(function ($arg) use ($argValues) {
                        if ($arg && $arg[0] === '$') {
                            return $argValues[mb_substr($arg, 1)];
                        }

                        return $arg;
                    }, $args);

                    $value = $this->faker->{$method}(...$args);
                }

                return [$key => $value];
            })->all();
    }

    protected function createMappingWithAction(Mapping $mapping, Owner $owner, ?Space $space): void
    {
        $owner->mappings()->save($mapping);
        if ($space) {
            $space->mappings()->save($mapping);
        }

        $mapping->recordAction($owner);
    }
}
