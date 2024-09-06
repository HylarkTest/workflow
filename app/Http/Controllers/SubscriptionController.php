<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Stripe\Plan;
use App\Models\Base;
use App\Models\User;
use Stripe\PromotionCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Subscription;
use App\Http\Resources\BillingResource;
use App\Events\Billing\SubscriptionCreated;
use Stripe\Exception\InvalidRequestException;
use App\Events\Billing\SubscriptionDowngraded;

class SubscriptionController extends Controller
{
    use InteractsWithPlans;

    public function __construct()
    {
        $this->middleware(['can:upgrade,'.Base::class]);
    }

    public function intent(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        session(['stripe_plan' => $request->input('plan')]);

        $intent = $user->createSetupIntent();

        return new JsonResponse([
            'intent' => $intent,
        ]);
    }

    public function index(): BillingResource
    {
        /** @var \App\Models\Base $base */
        $base = tenant();

        $subscriptions = $base->subscriptions->load('owner');

        $invoices = collect();

        $pageSize = 20;

        foreach ($subscriptions as $subscription) {
            try {
                $invoices = $invoices->merge($subscription->invoices(false, ['limit' => $pageSize]));
            } catch (InvalidRequestException $e) {
                report($e);
            }
            if ($invoices->count() >= $pageSize) {
                $invoices = $invoices->take($pageSize);
                break;
            }
        }

        return new BillingResource($base->getActiveSubscription(), $invoices);
    }

    /**
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var \App\Models\Base $base */
        $base = tenant();

        if (! $base->isSubscribed()) {
            $promotionCode = null;

            $checkFullDiscount = ! session()->has('stripe_plan');
            // If there isn't a plan in the session then it is a plan with a full discount.
            $period = session()->pull('stripe_plan') ?: $request->input('plan');
            $plan = $this->getPlan($period, $base);

            if ($request->input('coupon')) {
                $promotionCode = $this->getValidPromotionCodeFromCode($request->input('coupon'), $plan);
            }

            if ($promotionCode && $this->isFullDiscount($promotionCode)) {
                $paymentMethod = null;
            } else {
                abort_if($checkFullDiscount, 500);
                $paymentMethod = $request->validate($this->tokenRules())['paymentMethodId'];
            }

            // Not sure the best way to do this. We create a payment intent
            // instead of a setup intent. So they see the amount when they pay.
            // In order for them not to be charged twice, we set a trial period.
            $subscriptionBuilder = $user->newSubscription($base->premiumPlanName(), $plan->id);
            if ($promotionCode) {
                $subscriptionBuilder->withPromotionCode($promotionCode->id);
            }
            if ($base->isCollaborative()) {
                $subscriptionBuilder->quantity($base->members()->count());
            }
            $subscription = $subscriptionBuilder->create($paymentMethod, [
                'email' => $user->email,
                'name' => $user->name,
            ], [
                ...($paymentMethod ? [] : ['automatic_tax' => ['enabled' => false]]),
            ]);

            event(new SubscriptionCreated($user, $subscription));
        }

        return $this->successResponse();
    }

    public function update(Request $request): JsonResponse
    {
        $paymentMethod = $request->validate($this->tokenRules())['paymentMethodId'];

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var \App\Models\Base $base */
        $base = tenant();

        try {
            if (! $user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }
            $user->updateDefaultPaymentMethod($paymentMethod);

            if ($base->isCollaborative() && ($subscription = $base->getActiveSubscription())) {
                /** @var \App\Models\User $owner */
                $owner = $subscription->owner;
                if ($owner->isNot($user)) {
                    $this->createNewSubscriptionFromOld($subscription, $user);
                    if ($subscription->active()) {
                        $subscription->skipTrial()
                            ->noProrate()
                            ->cancelNow();
                    }
                }
            }

            info("Stripe card updated for user: $user->id");
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 422);
        }

        return $this->successResponse();
    }

    public function renew(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var \App\Models\Base $base */
        $base = tenant();

        $subscription = $base->getActiveSubscription();
        /** @phpstan-ignore-next-line  */
        if ($subscription && $subscription->owner->is($user)) {
            $subscription->resume();
            event(new SubscriptionCreated($user, $subscription));
        }

        return $this->successResponse();
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var \App\Models\Base $base */
        $base = tenant();

        $subscription = $base->getActiveSubscription();

        if ($subscription) {
            $subscription->cancel();

            info("Stripe subscription cancelled for user: $user->id", $subscription->toArray());
        }
        event(new SubscriptionDowngraded($user));

        return $this->successResponse();
    }

    protected function successResponse(array $response = []): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $response]);
    }

    protected function tokenRules(): array
    {
        return [
            'paymentMethodId' => [
                'required', function ($attribute, $value, $fail) {
                    if (($value['type'] ?? '') === 'alert') {
                        $fail($value['text']);
                    }
                },
            ],
        ];
    }

    protected function isFullDiscount(PromotionCode $promotionCode): bool
    {
        if (! $promotionCode->active) {
            return false;
        }
        $coupon = $promotionCode->coupon;

        if (! $coupon->valid) {
            return false;
        }

        if ($coupon->duration !== 'forever') {
            return false;
        }

        if ($coupon->percent_off !== 100.0) {
            return false;
        }

        return true;
    }

    protected function createNewSubscriptionFromOld(Subscription $oldSubscription, User $user): Subscription
    {
        $stripeSubscription = $oldSubscription->asStripeSubscription();
        $nextBilling = $stripeSubscription->current_period_end;
        /** @phpstan-ignore-next-line */
        $subscriptionBuilder = $user->newSubscription($oldSubscription->name, $stripeSubscription->plan->id);
        /** @phpstan-ignore-next-line  */
        if ($oldSubscription->quantity) {
            $subscriptionBuilder->quantity($oldSubscription->quantity);
        }
        // Figure out a way to do this that does not require a trial period.
        // Otherwise, new members will not be charged until the next billing cycle.
        $nextBilling = Carbon::createFromTimestamp($nextBilling);
        if ($nextBilling->isFuture()) {
            $subscriptionBuilder->anchorBillingCycleOn($nextBilling)->noProrate();
        }

        /** @phpstan-ignore-next-line */
        return $subscriptionBuilder->create($user->defaultPaymentMethod()->id, [
            'email' => $user->email,
            'name' => $user->name,
        ], ['automatic_tax' => ['enabled' => false]]);
    }
}
