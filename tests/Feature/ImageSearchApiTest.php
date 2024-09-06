<?php

declare(strict_types=1);

use Mockery\MockInterface;
use App\Core\GoogleSearchApi\Result;
use App\Core\GoogleSearchApi\CustomSearchGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it fetches list of images from google search api', function () {
    $user = createUser();

    $this->mock(CustomSearchGateway::class, function (MockInterface $mock) {
        $mock->shouldReceive('search')
            ->with('amazon', 10, 1)
            ->andReturnUsing(function () {
                return Result::asJson(file_get_contents(base_path('tests/files/CustomSearchApi/imageResults.json')));
            });
    });

    $this->be($user)
        ->getJson(route('image-search.index', ['query' => 'amazon', 10, 1]))
        ->assertJsonCount(10, 'data');
});
