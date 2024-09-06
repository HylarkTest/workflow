<?php

declare(strict_types=1);

use App\Models\Support\SupportTopic;
use App\Models\Support\ArticleStatus;
use Tests\Concerns\UsesElasticsearch;
use App\Models\Support\SupportArticle;
use App\Models\Support\SupportCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(UsesElasticsearch::class);
beforeEach(function () {
    /** @var \App\Models\Support\SupportCategory $category1 */
    $category1 = SupportCategory::query()->create([
        'name' => 'Category 1',
        'description' => 'Category 1 description',
        'is_public' => true,
    ]);
    /** @var \App\Models\Support\SupportCategory $category1 */
    $category2 = SupportCategory::query()->create([
        'name' => 'Category 2',
        'description' => 'Category 2 description',
        'is_public' => true,
    ]);

    /**
     * @var \App\Models\Support\SupportTopic $topic1
     * @var \App\Models\Support\SupportTopic $topic2
     * @var \App\Models\Support\SupportTopic $topic3
     * @var \App\Models\Support\SupportTopic $topic4
     */
    [$topic1, $topic2, $topic3, $topic4] = collect([1, 2, 3, 4])->map(fn ($num) => SupportTopic::query()->create([
        'name' => "topic{$num}",
    ]));

    /**
     * @var \App\Models\Support\SupportFolder $folder1
     * @var \App\Models\Support\SupportFolder $folder2
     */
    [$folder1, $folder2] = $category1->folders()->createMany([
        ['name' => 'Folder 1'],
        ['name' => 'Folder 2'],
    ]);

    /** @var \App\Models\Support\SupportFolder $folder3 */
    [$folder3] = $category2->folders()->createMany([
        ['name' => 'Folder 3'],
    ]);

    /** @var \App\Models\Support\SupportArticle $article1 */
    [$article1] = $folder1->articles()->createMany([[
        'status' => ArticleStatus::PUBLISHED->value,
        'title' => 'Article 1',
        'content' => '<h1>Article 1</h1>',
        'live_at' => now(),
        'created_at' => '2020-01-01 00:00:00',
    ]]);

    /**
     * @var \App\Models\Support\SupportArticle $article2
     * @var \App\Models\Support\SupportArticle $article3
     * @var \App\Models\Support\SupportArticle $article4
     */
    [$article2, $article3, $article4] = $folder2->articles()->createMany([
        [
            'status' => ArticleStatus::PUBLISHED->value,
            'title' => 'Article 2',
            'content' => '<h1>Article 2</h1>',
            'views' => 10,
            'thumbs_up' => 3,
            'thumbs_down' => 2,
            'live_at' => now(),
            'created_at' => '2020-01-02 00:00:00',
        ],
        [
            'status' => ArticleStatus::PUBLISHED->value,
            'title' => 'Article 3',
            'content' => '<h1>Article 3</h1>',
            'views' => 5,
            'thumbs_up' => 2,
            'thumbs_down' => 0,
            'live_at' => now(),
            'created_at' => '2020-01-03 00:00:00',
        ],
        [
            'status' => ArticleStatus::PUBLISHED->value,
            'title' => 'Article 4',
            'content' => '<h1>Article 4</h1>',
            'views' => 20,
            'thumbs_up' => 0,
            'thumbs_down' => 0,
            'live_at' => now(),
            'created_at' => '2020-01-04 00:00:00',
        ],
    ]);

    $article1->topics()->attach([$topic1->id, $topic2->id]);
    $article2->topics()->attach([$topic3->id, $topic4->id]);
    $article3->topics()->attach([$topic1->id]);
    $article4->topics()->attach([$topic1->id, $topic2->id]);

    SupportArticle::makeAllSearchable();
});

test('all articles can be fetched', function () {
    $user = createUser();
    $this->be($user)->get(route('support.index'))
        ->assertSuccessful()
        ->assertExactJson(['data' => [
            [
                'createdAt' => '2020-01-01 00:00:00',
                'descriptionTrimmed' => 'Article 1',
                'friendlyUrl' => 'article-1',
                'hits' => 0,
                'id' => 1,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 1',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
            [
                'createdAt' => '2020-01-02 00:00:00',
                'descriptionTrimmed' => 'Article 2',
                'friendlyUrl' => 'article-2',
                'hits' => 10,
                'id' => 2,
                'thumbsDown' => 2,
                'thumbsUp' => 3,
                'title' => 'Article 2',
                'topics' => [
                    ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                    ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                ],
            ],
            [
                'createdAt' => '2020-01-03 00:00:00',
                'descriptionTrimmed' => 'Article 3',
                'friendlyUrl' => 'article-3',
                'hits' => 5,
                'id' => 3,
                'thumbsDown' => 0,
                'thumbsUp' => 2,
                'title' => 'Article 3',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                ],
            ],
            [
                'createdAt' => '2020-01-04 00:00:00',
                'descriptionTrimmed' => 'Article 4',
                'friendlyUrl' => 'article-4',
                'hits' => 20,
                'id' => 4,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 4',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
        ]]);
});

test('top 3 most recent articles can be fetched', function () {
    $user = createUser();
    $this->be($user)->get(route('support.index', ['recent' => true]))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertExactJson(['data' => [
            [
                'createdAt' => '2020-01-04 00:00:00',
                'descriptionTrimmed' => 'Article 4',
                'friendlyUrl' => 'article-4',
                'hits' => 20,
                'id' => 4,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 4',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
            [
                'createdAt' => '2020-01-03 00:00:00',
                'descriptionTrimmed' => 'Article 3',
                'friendlyUrl' => 'article-3',
                'hits' => 5,
                'id' => 3,
                'thumbsDown' => 0,
                'thumbsUp' => 2,
                'title' => 'Article 3',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                ],
            ],
            [
                'createdAt' => '2020-01-02 00:00:00',
                'descriptionTrimmed' => 'Article 2',
                'friendlyUrl' => 'article-2',
                'hits' => 10,
                'id' => 2,
                'thumbsDown' => 2,
                'thumbsUp' => 3,
                'title' => 'Article 2',
                'topics' => [
                    ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                    ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                ],
            ],
        ]]);
});

test('top 3 recommended articles can be fetched', function () {
    $user = createUser();

    $this->be($user)->get(route('support.index', ['recommended' => true]))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertExactJson(['data' => [
            [
                'createdAt' => '2020-01-03 00:00:00',
                'descriptionTrimmed' => 'Article 3',
                'friendlyUrl' => 'article-3',
                'hits' => 5,
                'id' => 3,
                'thumbsDown' => 0,
                'thumbsUp' => 2,
                'title' => 'Article 3',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                ],
            ],
            [
                'createdAt' => '2020-01-02 00:00:00',
                'descriptionTrimmed' => 'Article 2',
                'friendlyUrl' => 'article-2',
                'hits' => 10,
                'id' => 2,
                'thumbsDown' => 2,
                'thumbsUp' => 3,
                'title' => 'Article 2',
                'topics' => [
                    ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                    ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                ],
            ],
            [
                'createdAt' => '2020-01-04 00:00:00',
                'descriptionTrimmed' => 'Article 4',
                'friendlyUrl' => 'article-4',
                'hits' => 20,
                'id' => 4,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 4',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
        ]]);
});

test('articles can be searched by title', function () {
    $user = createUser();
    $this->be($user)->get(route('support.index', ['search' => 'Article', 'topics' => ['topic3', 'topic2']]))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertExactJson(['data' => [
            [
                'createdAt' => '2020-01-01 00:00:00',
                'descriptionTrimmed' => 'Article 1',
                'friendlyUrl' => 'article-1',
                'hits' => 0,
                'id' => 1,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 1',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
            [
                'createdAt' => '2020-01-02 00:00:00',
                'descriptionTrimmed' => 'Article 2',
                'friendlyUrl' => 'article-2',
                'hits' => 10,
                'id' => 2,
                'thumbsDown' => 2,
                'thumbsUp' => 3,
                'title' => 'Article 2',
                'topics' => [
                    ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                    ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                ],
            ],
            [
                'createdAt' => '2020-01-04 00:00:00',
                'descriptionTrimmed' => 'Article 4',
                'friendlyUrl' => 'article-4',
                'hits' => 20,
                'id' => 4,
                'thumbsDown' => 0,
                'thumbsUp' => 0,
                'title' => 'Article 4',
                'topics' => [
                    ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                    ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                ],
            ],
        ]]);
})->group('es');

test('categories can be fetched', function () {
    $user = createUser();
    $this->be($user)->get(route('support.categories'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertExactJson([
            'data' => [
                [
                    'description' => 'Category 1 description',
                    'folders' => [
                        [
                            'articles' => [
                                [
                                    'createdAt' => '2020-01-01 00:00:00',
                                    'descriptionTrimmed' => 'Article 1',
                                    'friendlyUrl' => 'article-1',
                                    'hits' => 0,
                                    'id' => 1,
                                    'thumbsDown' => 0,
                                    'thumbsUp' => 0,
                                    'title' => 'Article 1',
                                    'topics' => [
                                        ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                                        ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                                    ],
                                ],
                            ],
                            'articlesCount' => 1,
                            'id' => 1,
                            'name' => 'Folder 1',
                        ],
                        [
                            'articles' => [
                                [
                                    'createdAt' => '2020-01-02 00:00:00',
                                    'descriptionTrimmed' => 'Article 2',
                                    'friendlyUrl' => 'article-2',
                                    'hits' => 10,
                                    'id' => 2,
                                    'thumbsDown' => 2,
                                    'thumbsUp' => 3,
                                    'title' => 'Article 2',
                                    'topics' => [
                                        ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                                        ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                                    ],
                                ],
                                [
                                    'createdAt' => '2020-01-03 00:00:00',
                                    'descriptionTrimmed' => 'Article 3',
                                    'friendlyUrl' => 'article-3',
                                    'hits' => 5,
                                    'id' => 3,
                                    'thumbsDown' => 0,
                                    'thumbsUp' => 2,
                                    'title' => 'Article 3',
                                    'topics' => [['id' => 1, 'key' => 'topic1', 'name' => 'topic1']],
                                ],
                                [
                                    'createdAt' => '2020-01-04 00:00:00',
                                    'descriptionTrimmed' => 'Article 4',
                                    'friendlyUrl' => 'article-4',
                                    'hits' => 20,
                                    'id' => 4,
                                    'thumbsDown' => 0,
                                    'thumbsUp' => 0,
                                    'title' => 'Article 4',
                                    'topics' => [
                                        ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                                        ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                                    ],
                                ],
                            ],
                            'articlesCount' => 3,
                            'id' => 2,
                            'name' => 'Folder 2',
                        ],
                    ],
                    'id' => 1,
                    'name' => 'Category 1',
                ],
                [
                    'description' => 'Category 2 description',
                    'folders' => [
                        [
                            'articles' => [],
                            'articlesCount' => 0,
                            'id' => 3,
                            'name' => 'Folder 3',
                        ],
                    ],
                    'id' => 2,
                    'name' => 'Category 2',
                ],
            ],
        ]);
});

test('top 3 popular categories can be fetched', function () {
    $user = createUser();
    $this->be($user)->get(route('support.categories', ['popular' => true]))
        ->assertSuccessful()
        ->assertExactJson(['data' => [
            [
                'articles' => [
                    [
                        'createdAt' => '2020-01-04 00:00:00',
                        'descriptionTrimmed' => 'Article 4',
                        'friendlyUrl' => 'article-4',
                        'hits' => 20,
                        'id' => 4,
                        'thumbsDown' => 0,
                        'thumbsUp' => 0,
                        'title' => 'Article 4',
                        'topics' => [
                            ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                            ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                        ],
                    ],
                    [
                        'createdAt' => '2020-01-02 00:00:00',
                        'descriptionTrimmed' => 'Article 2',
                        'friendlyUrl' => 'article-2',
                        'hits' => 10,
                        'id' => 2,
                        'thumbsDown' => 2,
                        'thumbsUp' => 3,
                        'title' => 'Article 2',
                        'topics' => [
                            ['id' => 3, 'key' => 'topic3', 'name' => 'topic3'],
                            ['id' => 4, 'key' => 'topic4', 'name' => 'topic4'],
                        ],
                    ],
                    [
                        'createdAt' => '2020-01-03 00:00:00',
                        'descriptionTrimmed' => 'Article 3',
                        'friendlyUrl' => 'article-3',
                        'hits' => 5,
                        'id' => 3,
                        'thumbsDown' => 0,
                        'thumbsUp' => 2,
                        'title' => 'Article 3',
                        'topics' => [
                            ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                        ],
                    ],
                ],
                'description' => 'Category 1 description',
                'id' => 1,
                'name' => 'Category 1',
            ],
        ]]);
});

test('topics can be fetched ordered by popularity', function () {
    $user = createUser();
    $this->be($user)->get(route('support.topics', ['popular' => true]))
        ->assertSuccessful()
        ->assertExactJson(['data' => [
            [
                'id' => 1,
                'key' => 'topic1',
                'name' => 'topic1',
            ],
            [
                'id' => 2,
                'key' => 'topic2',
                'name' => 'topic2',
            ],
            [
                'id' => 3,
                'key' => 'topic3',
                'name' => 'topic3',
            ],
            [
                'id' => 4,
                'key' => 'topic4',
                'name' => 'topic4',
            ],
        ]]);
});

test('a specific folder can be fetched', function () {
    $user = createUser();
    $this->be($user)->get(route('support.showFolder', ['id' => 1]))
        ->assertSuccessful()
        ->assertExactJson([
            'data' => [
                'articles' => [
                    [
                        'createdAt' => '2020-01-01 00:00:00',
                        'descriptionTrimmed' => 'Article 1',
                        'friendlyUrl' => 'article-1',
                        'hits' => 0,
                        'id' => 1,
                        'thumbsDown' => 0,
                        'thumbsUp' => 0,
                        'title' => 'Article 1',
                        'topics' => [
                            ['id' => 1, 'key' => 'topic1', 'name' => 'topic1'],
                            ['id' => 2, 'key' => 'topic2', 'name' => 'topic2'],
                        ],
                    ],
                ],
                'articlesCount' => 1,
                'id' => 1,
                'name' => 'Folder 1',
            ],
        ]);
});
