<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\GoogleSearchApi\Image;
use Illuminate\Http\Client\RequestException;

class ImageSearchController extends Controller
{
    /**
     * @throws RequestException
     * @throws \JsonException
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:3|max:255',
            'first' => 'sometimes|integer|min:1|max:10',
            'start' => 'sometimes|integer|min:1',
        ]);

        /** @var string $query */
        $query = $request->query('query');
        $query = remove_special_chars($query);
        $first = (int) $request->query('first', config('services.search_api.results_count'));
        $start = (int) $request->query('start', '1');

        try {
            $results = Image::search($query, $first, $start);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            report($e);

            return response()->json(['error' => trans('errors.image_searches.unavailable')], 400);
        }

        return response()->json(['data' => $results]);
    }
}
