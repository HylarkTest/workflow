<?php

declare(strict_types=1);

use App\Models\Page;
use App\Core\Pages\PageType;
use Illuminate\Http\UploadedFile;

test('a page can be saved with a folder', function () {
    $page = new Page([
        'name' => 'Sewing projects',
        'folder' => 'SEWING',
        'symbol' => 'fa-needle',
        'type' => PageType::ENTITIES,
    ]);

    expect($page->path)->toBe('SEWING/Sewing projects');
});

test('the page can be saved with a name and folder', function () {
    $page = new Page([
        'name' => 'page',
        'folder' => 'folder',
        'type' => PageType::ENTITIES,
    ]);

    expect($page->path)->toBe('folder/page')
        ->and($page->folder)->toBe('folder/')
        ->and($page->name)->toBe('page');
});

test('the page name can override the folder', function () {
    $page = new Page([
        'path' => 'folder/page',
    ]);

    $page->name = 'new page';

    expect($page->path)->toBe('folder/new page');

    $page->name = 'new folder/newer page';

    expect($page->path)->toBe('new folder/newer page');
});

test('a page can have no folder', function () {
    $page = new Page([
        'name' => 'page',
    ]);

    expect($page->path)->toBe('page')
        ->and($page->name)->toBe('page')
        ->and($page->folder)->toBe('');
});

test('a page can be saved with an image', function () {
    $file = UploadedFile::fake()->image('image.jpg');

    $page = new Page([
        'name' => 'page',
        'type' => PageType::ENTITIES,
        'image' => 'page-images/'.$file->hashName(),
    ]);

    expect($page->image)->toBe('page-images/'.$file->hashName());
});
