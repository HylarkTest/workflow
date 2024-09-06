<?php

declare(strict_types=1);

namespace Hylark\ArticleContent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Support\SupportArticle;

class ArticleController
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => SupportArticle::query()
                ->get()
                ->map(fn (SupportArticle $article) => [
                    'id' => $article->getKey(),
                    'title' => $article->title,
                    'url' => $article->url(),
                ]),
        ]);
    }
}
