<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Stripe\Plan;
use Stripe\Coupon;
use App\Models\Base;
use Stripe\PromotionCode;
use Laravel\Cashier\Cashier;

trait InteractsWithPlans
{
    protected function getPlan(string $id, Base $base): Plan
    {
        $name = $base->premiumPlanName();
        $planId = $id === 'YEAR'
            ? config("pricing.plans.$name.yearly_stripe_id")
            : config("pricing.plans.$name.monthly_stripe_id");

        /** @var \Stripe\Plan $plan */
        $plan = Cashier::stripe()->plans->retrieve($planId);

        return $plan;
    }

    protected function getValidCouponFromCode(string $code, Plan $plan): Coupon
    {
        return $this->getValidPromotionCodeFromCode($code, $plan)->coupon;
    }

    protected function getValidPromotionCodeFromCode(string $code, Plan $plan): PromotionCode
    {
        $code = $this->findPromotionCode($code);

        abort_if(! $code?->active, 404);

        $coupon = $code->coupon;

        abort_if(! $coupon->valid, 404);

        abort_if(! $this->couponAppliesToPlan($coupon, $plan), 404);

        return $code;
    }

    protected function couponAppliesToPlan(Coupon $coupon, Plan $plan): bool
    {
        if (! $coupon->applies_to) {
            return true;
        }

        /** @phpstan-ignore-next-line */
        return \in_array($plan->id, $coupon->applies_to->products, true);
    }

    protected function findPromotionCode(string $code): ?PromotionCode
    {
        $codes = Cashier::stripe()->promotionCodes->all(array_merge([
            'code' => $code,
            'limit' => 1,
        ]));

        return $codes->first();
    }
}
