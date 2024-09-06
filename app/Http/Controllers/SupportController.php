<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Models\Support\SupportArticle;
use Illuminate\Validation\ValidationException;
use App\Core\Support\DatabaseSupportRepository;

class SupportController extends Controller
{
    public function __construct(protected DatabaseSupportRepository $supportRepository) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->query('recent')) {
            $articles = $this->supportRepository->getMostRecentArticles((array) $request->query('topics'));
        } elseif ($request->query('recommended')) {
            $articles = $this->supportRepository->getRecommendedArticles((array) $request->query('topics'));
        } elseif ($request->query('search')) {
            /** @phpstan-ignore-next-line */
            $articles = $this->supportRepository->searchArticles((string) $request->query('search'), $request->query('topics'));
        } else {
            $articles = $this->supportRepository->getSupportArticles();
        }

        return response()->json([
            'data' => $articles,
        ]);
    }

    public function indexCategories(Request $request): JsonResponse
    {
        if ($request->query('popular')) {
            $categories = $this->supportRepository->getPopularCategories();
        } else {
            $categories = $this->supportRepository->getSupportCategories();
        }

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function indexTopics(Request $request): JsonResponse
    {
        if ($request->query('popular')) {
            $topics = $this->supportRepository->getPopularTopics();
        } else {
            $topics = $this->supportRepository->getTopics();
        }

        return response()->json([
            'data' => $topics,
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        try {
            /** @phpstan-ignore-next-line */
            $article = $this->supportRepository->getArticle($request->route('id'));
        } catch (\Throwable) {
            abort(404);
        }

        if ($article['description'] ?? null) {
            $article['description'] = preg_replace(
                [
                    '#href="https://hylark\.freshdesk\.com/(\w{2})?/support/solutions/articles/(\d+)"#',
                    '#href="https://app\.hylark\.com([^"]*)"#',
                ],
                [
                    'href="/support/article/$2"',
                    'href="'.config('app.url').'$1"',
                ],
                $article['description']
            );
        }

        return response()->json([
            'data' => $article,
        ]);
    }

    public function showFolder(Request $request): JsonResponse
    {
        try {
            /** @phpstan-ignore-next-line */
            $folder = $this->supportRepository->getFolder((int) $request->route('id'));
        } catch (\Throwable) {
            abort(404);
        }

        return response()->json([
            'data' => $folder,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'type' => 'required|string|in:QUESTION,REPORT_BUG,FEEDBACK,MY_ACCOUNT,FEATURE_REQUEST,OTHER',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ]);

        if ($data['attachments']) {
            /** @var array<int, \Illuminate\Http\UploadedFile> $attachments */
            $attachments = $data['attachments'];
            $totalSize = collect($attachments)->sum->getSize();
            if ($totalSize > 20 * 1024 * 1024) {
                throw ValidationException::withMessages(['attachments' => trans('validation.max.file_MB', ['attribute' => 'attachments', 'max' => 20])]);
            }
        }

        $subject = app()->environment('production') ? $data['subject'] : '[TEST]: '.$data['subject'];

        $client = Http::withBasicAuth(config('services.freshdesk.api_key'), 'X');

        foreach ($data['attachments'] as $attachment) {
            $client->attach('attachments[]', $attachment->get(), $attachment->getClientOriginalName());
        }

        $response = $client->asMultipart()->post(config('services.freshdesk.url').'/tickets', [
            'name' => $user->name,
            'email' => $user->email,
            'type' => $data['type'],
            'subject' => $subject,
            'description' => $data['description'],
            'priority' => $user->ownsPremiumBase() ? 2 : 1,
            'source' => 100,
            'status' => 2,
            // 'product_id' => (int) config('services.freshdesk.product_id'),
            'group_id' => (int) config('services.freshdesk.group_id'),
        ]);

        if ($response->status() !== 201) {
            /** @phpstan-ignore-next-line  */
            report(json_encode($response->json()));

            return response()->json([
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'Your support request has been sent.',
        ], 200);
    }

    public function incrementView(string $articleId): JsonResponse
    {
        return $this->incrementField($articleId, 'views');
    }

    public function incrementThumbsUp(string $articleId): JsonResponse
    {
        return $this->incrementField($articleId, 'thumbs_up');
    }

    public function incrementThumbsDown(string $articleId): JsonResponse
    {
        return $this->incrementField($articleId, 'thumbs_down');
    }

    protected function incrementField(string $articleId, string $field): JsonResponse
    {
        $article = SupportArticle::findByIdOrUrl($articleId);
        $parent = $article->parent;
        if ($parent) {
            $editableArticle = $parent;
        } else {
            $editableArticle = $article;
        }
        if (config('hylark.support.database') === 'resources' && app()->environment('production')) {
            $editableArticle->{$field}++;
            $editableArticle->timestamps = false;
            $editableArticle->save();
        }

        return response()->json([
            'data' => $article,
        ]);
    }
}
