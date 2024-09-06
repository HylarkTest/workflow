<?php

declare(strict_types=1);

namespace Tests\Actions\Feature;

use LighthouseHelpers\Utils;
use Tests\Actions\ModelWithAction;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Actions\Models\Concerns\PerformsActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class ActionFiltersDirectiveTest extends FeatureTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * Models can be filtered by who created
     *
     * @test
     */
    public function models_can_be_filtered_by_who_created(): void
    {
        config(['actions.automatic' => false]);
        $firstUser = User::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);
        $secondUser = User::query()->forceCreate([
            'name' => 'Toby',
            'email' => 't@o.by',
            'password' => 'secret',
        ]);

        /** @var \Tests\Actions\ModelWithAction $firstModel */
        $firstModel = ModelWithAction::query()->forceCreate([
            'name' => 'Garry',
        ]);
        $firstModel->recordAction($firstUser);

        /** @var \Tests\Actions\ModelWithAction $secondModel */
        $secondModel = ModelWithAction::query()->forceCreate([
            'name' => 'Terry',
        ]);
        $secondModel->recordAction($secondUser);

        $firstModel = $firstModel->fresh();
        $firstModel->forceFill(['name' => 'Terry'])->save();
        $firstModel->recordAction($firstUser);

        $secondModel = $secondModel->fresh();
        $secondModel->forceFill(['name' => 'Terry'])->save();
        $secondModel->recordAction($secondUser);

        $firstId = base64_encode('User:1');
        $secondId = base64_encode('User:2');
        $this->graphQL("
        {
            createdModels: modelsWithAction(createdBy: [\"$firstId\"]) { id }
            updatedModels: modelsWithAction(lastUpdatedBy: [\"$secondId\"]) { id }
        }
        ")->assertJson(['data' => [
            'createdModels' => [['id' => base64_encode('ModelWithAction:1')]],
            'updatedModels' => [['id' => base64_encode('ModelWithAction:2')]],
        ]], true);
    }

    /**
     * Two different types of performer can be filtered
     *
     * @test
     */
    public function two_different_types_of_performer_can_be_filtered(): void
    {
        Schema::create('other_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        config(['actions.automatic' => false]);

        $firstUser = User::query()->forceCreate([
            'name' => 'Larry',
            'email' => 'l@r.ry',
            'password' => 'secret',
        ]);
        $secondUser = OtherUser::query()->forceCreate([
            'name' => 'Toby',
        ]);

        /** @var \Tests\Actions\ModelWithAction $firstModel */
        $firstModel = ModelWithAction::query()->forceCreate([
            'name' => 'Garry',
        ]);
        $firstModel->recordAction($firstUser);

        /** @var \Tests\Actions\ModelWithAction $secondModel */
        $secondModel = ModelWithAction::query()->forceCreate([
            'name' => 'Terry',
        ]);
        $secondModel->recordAction($secondUser);

        $firstId = base64_encode('User:1');
        $this->graphQL("
        {
            createdModels: modelsWithAction(createdBy: [\"$firstId\"]) { id }
        }
        ")->assertJsonCount(1, 'data.createdModels');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'lighthouse.namespaces.models' => array_merge(
                [__NAMESPACE__, 'Illuminate\\Foundation\\Auth'],
                config('lighthouse.namespaces.models')
            ),
        ]);

        Utils::clearCache();
    }
}

class OtherUser extends Model
{
    use PerformsActions;

    public string $performerDisplayNameKey = 'name';
}
