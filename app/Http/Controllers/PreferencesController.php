<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserPreferencesRequest;

class PreferencesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return new JsonResponse($user->settings->settings->toArray());
    }

    public function update(UserPreferencesRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $settings = $user->settings->settings;

        /** @var \App\Models\UserSettings $settings */
        $settings = $user->settings()->updateOrCreate([], ['settings' => array_merge($settings->toArray(), $request->validated())]);

        return new JsonResponse($settings->settings->toArray());
    }
}
