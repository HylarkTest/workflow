<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\LoginAttemptResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoginHistoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $loginHistory = $user->successfulLoginAttempts()->latest('id')->paginate();

        return LoginAttemptResource::collection($loginHistory);
    }
}
