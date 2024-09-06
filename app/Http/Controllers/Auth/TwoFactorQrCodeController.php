<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController as BaseTwoFactorQrCodeController;

class TwoFactorQrCodeController extends BaseTwoFactorQrCodeController
{
    public function show(Request $request): array|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user->two_factor_secret === null) {
            return [];
        }

        return response()->json([
            'svg' => $user->twoFactorQrCodeSvg(),
            'url' => $user->twoFactorQrCodeUrl(),
            'code' => decrypt($user->two_factor_secret),
        ]);
    }
}
