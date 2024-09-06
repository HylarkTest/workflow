<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    use InteractsWithPlans;

    public function show(string $plan, string $code, Request $request): JsonResponse
    {
        /** @var \App\Models\Base $base */
        $base = tenant();

        $plan = $this->getPlan($plan, $base);

        $coupon = $this->getValidCouponFromCode($code, $plan);

        return new JsonResponse([
            'data' => [
                'id' => $coupon->id,
                'name' => $coupon->name,
                'amountOff' => $coupon->amount_off,
                'percentOff' => $coupon->percent_off,
                'duration' => $coupon->duration_in_months,
                'code' => $code,
            ],
        ]);
    }
}
