<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use App\Models\Mapping;
use Illuminate\Http\File;
use App\Models\MarkerGroup;
use Illuminate\Database\Seeder;
use Intervention\Image\ImageManager;
use Illuminate\Foundation\Testing\WithFaker;
use Mappings\Core\Documents\Contracts\DocumentRepository;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class DemoSeeder extends Seeder
{
    // use WithFaker;
    use SeedsJsonFiles;

    /**
     * Run the database seeds.
     */
    public function run(\Faker\Generator $faker): void
    {
        $executive = factory(User::class)->states('person')->create();

        $this->seedDirectory(__DIR__.'/../mappings/executives-place', $executive);

        // $this->setUpFaker();

        $user = factory(User::class)->states('person')->create();

        $mappings = collect(['people' => 10, 'projects' => 20])
            ->map(static function (int $count, string $type) use ($user) {
                $mapping = $user->mappings()->save(factory(Mapping::class)->state("demo:$type")->make());
                $user->spaces->first()->mappings()->save($mapping);
                $mapping->items()->saveMany(factory(Item::class, $count)->state("demo:$type")->make())
                    ->each(fn (Item $item) => $item->recordAction($user));
                $mapping->recordAction($user);

                return $mapping;
            })
            ->values()->all();

        $this->hookUpRelationships(...$mappings);

        //        $tagGroups = collect(['status'])
        //            ->map(fn(string $type) => tap(factory(TagGroup::class)->state("demo:$type")->create([
        //                'owner_type' => $user->getMorphClass(),
        //                'owner_id' => $user,
        //            ]))->recordAction($user));

        // this->hookUpTagGroups(...$mappings, ...$tagGroups);
    }

    protected function hookUpRelationships(Mapping $people, Mapping $projects): void
    {
        /** @var \Mappings\Core\Mappings\Relationships\Relationship $rel */
        $rel = $people->addRelationship([
            'type' => RelationshipType::ONE_TO_MANY,
            'to' => $projects,
        ]);

        $projects->addRelationship([
            'id' => $rel->id(),
            'type' => RelationshipType::MANY_TO_ONE,
            'to' => $people,
            'inverse' => true,
        ]);

        $people->items->each(fn (Item $person) => $rel->add($person, $projects->items->random($this->faker->numberBetween(1, 5))));
    }

    protected function hookUpTagGroups(
        Mapping $people,
        Mapping $projects,
        MarkerGroup $status
    ): void {
        //        /** @var \App\Core\Mappings\Tags\MappingTagGroup $group */
        //        $group = $projects->addTagGroup([
        //            'name' => 'Pipeline',
        //            'group' => $recruitment,
        //            'type' => TagType::PIPELINE,
        //            'relationship' => $assignments->relationships->firstWhere('name', 'Candidates'),
        //        ]);
        //
        //        $assignments->addTagGroup([
        //            'name' => 'Status',
        //            'group' => $status,
        //            'type' => TagType::STATUS,
        //        ]);
        //
        //        $assignments->items->each(fn(Item $assignment) => $assignment->tags()->attach($status->tags->random()));
        //
        //        $people->addTagGroup([
        //            'name' => 'Tags',
        //            'group' => $executive,
        //            'type' => TagType::DESCRIPTIVE,
        //        ]);
        //
        //        $people->addTagGroup([
        //            'id' => $group->id(),
        //            'name' => 'Assignments',
        //            'group' => $recruitment,
        //            'type' => TagType::PIPELINE,
        //            'relationship' => $group->relationship,
        //        ]);
        //
        //        $people->items->each(fn(Item $candidate) => $candidate->tags()->attach($executive->tags->random(3)));
        //
        //        $companies->addTagGroup([
        //            'name' => 'Status',
        //            'group' => $status,
        //            'type' => TagType::STATUS,
        //        ]);
        //
        //        $clients->items->each(fn(Item $client) => $client->tags()->attach($status->tags->random()));
    }

    protected function saveCroppedImage(string $url, $width = 400): array
    {
        $image = (new ImageManager)->make($url)->save(sys_get_temp_dir().'/cropped-image');
        $originalDocument = resolve(DocumentRepository::class)->store(new File($image->basePath()));
        $image->crop($width, $width, 20, 20)->save();
        $document = resolve(DocumentRepository::class)->store(new File($image->basePath()));

        return [
            'originalImage' => $originalDocument->id(),
            'image' => $document->id(),
            'width' => 100,
            'height' => 100,
            'xOffset' => 20,
            'yOffset' => 20,
        ];
    }
}
