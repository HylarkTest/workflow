<?php

declare(strict_types=1);

use App\Models\Support\ArticleStatus;
use App\Models\Support\SupportArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('The friendly URL is created from the title if not set', function () {
    $article = new SupportArticle([
        'title' => 'Need help?',
    ]);

    expect($article->friendly_url)->toBe('need-help');
});

test('Drafts are not queried by default', function () {
    SupportArticle::factory()->create([
        'status' => ArticleStatus::DRAFT,
        'live_at' => null,
    ]);

    expect(SupportArticle::query()->count())->toBe(0);
    expect(SupportArticle::query()->withoutGlobalScopes(['published', 'live'])->count())->toBe(1);
});

test('Versions cannot change their status', function () {
    $article = SupportArticle::factory()->create(['content' => '<a href="example.com">Example</a>']);
    $version = $article->versions()->save(
        SupportArticle::factory()->make([
            'status' => ArticleStatus::DRAFT,
            'live_at' => null,
        ])
    );

    $version->update(['status' => ArticleStatus::PUBLISHED]);
})->throws(Exception::class, 'Cannot update an article version');

test('The cache is busted when a support article is updated', function () {
    $article = SupportArticle::factory()->create([
        'title' => 'Article',
        'content' => 'Content',
    ]);

    $article = SupportArticle::getCachedArticle($article->friendly_url);
    expect($article->content)->toBe('Content');

    $article->update(['content' => 'New content']);

    $article = SupportArticle::getCachedArticle($article->friendly_url);
    expect($article->content)->toBe('New content');
});

test('An article cannot be deleted if there are other live articles linking to it', function () {
    /** @var \App\Models\Support\SupportArticle $article */
    $article = SupportArticle::factory()->create([
        'title' => 'Article',
    ]);

    SupportArticle::factory()->create([
        'title' => 'Linked article',
        'content' => '<a href="'.$article->url().'">Article</a>',
    ]);

    $article->delete();
})->throws(Exception::class, 'Cannot delete article with linked articles');

test('If the friendly URL is changed, any articles linking to it are updated', function () {
    /** @var \App\Models\Support\SupportArticle $article */
    $article = SupportArticle::factory()->create([
        'title' => 'Article',
    ]);

    $linkedArticle = SupportArticle::factory()->create([
        'title' => 'Linked article',
        'content' => '<a href="'.$article->url().'">Article</a>',
    ]);

    $linkedDraftArticle = SupportArticle::factory()->create([
        'title' => 'Linked draft article',
        'content' => '<a href="'.$article->url().'">Article</a>',
        'status' => ArticleStatus::DRAFT,
        'live_at' => null,
    ]);

    $article->update(['friendly_url' => 'new-article']);

    expect($linkedArticle->fresh()->content)->toBe('<a href="'.$article->url().'">Article</a>');
    expect($linkedDraftArticle->fresh()->content)->toBe('<a href="'.$article->url().'">Article</a>');
});

test('An article cannot be made live if another live article has the same friendly URL', function () {
    $article = SupportArticle::factory()->create([
        'title' => 'Article',
        'friendly_url' => 'article',
    ]);
    $draft = SupportArticle::factory()->create([
        'title' => 'Draft',
        'friendly_url' => 'article',
        'status' => ArticleStatus::DRAFT,
        'live_at' => null,
    ]);

    $draft->update([
        'status' => ArticleStatus::PUBLISHED,
        'live_at' => now(),
    ]);
})->throws(Exception::class, 'An article with the same friendly URL already exists');

test('Creating a new version does not change the recent articles', function () {
    $articleA = SupportArticle::factory()->create([
        'title' => 'Article A',
    ]);
    $this->travel(2)->seconds();
    $articleB = SupportArticle::factory()->create([
        'title' => 'Article B',
    ]);

    expect(SupportArticle::getCachedMostRecentArticles())->get(0)->id->toBe($articleB->id);

    $this->travel(2)->seconds();
    $articleA->createVersion();

    expect(SupportArticle::getCachedMostRecentArticles())->get(0)->id->toBe($articleB->id);
});

test('Publishing a new version retains the previous live_at time', function () {
    $article = SupportArticle::factory()->create([
        'title' => 'Article',
    ]);
    $article->publish();

    $liveAt = $article->live_at;
    $this->travel(10)->minutes();
    $article->createVersion();

    $article = $article->fresh();
    expect($article->live_at)->toBe(null);
    expect($article->status)->toBe(ArticleStatus::DRAFT);

    $article->publish();
    expect($article->live_at->format('YmdHis'))->toBe($liveAt->format('YmdHis'));
    expect($article->status)->toBe(ArticleStatus::PUBLISHED);
});
