<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;
use Hylark\ArticleContent\Http\Middleware\Middleware;
use Hylark\ArticleContent\Http\Controllers\ImageController;
use Hylark\ArticleContent\Http\Controllers\ArticleController;

// Without CSRF protection
Route::post('/upload', [ImageController::class, 'upload'])->name('article-content.upload')
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware(Middleware::class);

Route::get('/articles', [ArticleController::class, 'index'])->name('article-content.index');
