<?php

declare(strict_types=1);

use Stripe\Plan;
use Carbon\Carbon;
use Stripe\Coupon;
use Stripe\Discount;
use LighthouseHelpers\Utils;
use Illuminate\Support\Facades\DB;
use App\Models\Contracts\NotScoped;
use Illuminate\Database\Connection;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as RawBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

function array_diff_recursive(array $a1, array $a2): array
{
    $result = [];

    foreach ($a1 as $key => $value) {
        if (array_key_exists($key, $a2)) {
            if (is_array($value) && is_array($a2[$key])) {
                $diff = array_diff_recursive($value, $a2[$key]);
                if (count($diff)) {
                    $result[$key] = $diff;
                }
            } elseif ($value !== $a2[$key]) {
                $result[$key] = $value;
            }
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}

function array_intersect_key_recursive(array $a1, array $a2): array
{
    $result = [];

    foreach ($a1 as $key => $value) {
        if (array_key_exists($key, $a2)) {
            if (is_array($value)) {
                $diff = array_intersect_key_recursive($value, $a2[$key]);
                if (count($diff)) {
                    $result[$key] = $diff;
                }
            } else {
                $result[$key] = $value;
            }
        }
    }

    return $result;
}

/**
 * @param  mixed  $item
 */
function is_string_castable($item): bool
{
    return ! is_array($item)
        && ((! is_object($item) && settype($item, 'string') !== false)
        || (is_object($item) && method_exists($item, '__toString')));
}

function increment_string_suffix(string $value): string
{
    $value = preg_replace_callback('/\d*$/', static function (array $matches): string {
        $number = $matches[0] ?: '1';
        $numberLength = mb_strlen($number);

        return str_pad((string) ((int) $number + 1), $numberLength, '0', \STR_PAD_LEFT);
    }, $value, 1);

    return $value ?: '';
}

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  class-string<TModel>  $model
 * @param  array<string, mixed>  $attributes
 * @param  array  $args
 * @return TModel|\Illuminate\Database\Eloquent\Collection<int, TModel>
 */
function create(string $model, array $attributes = [], ...$args)
{
    /** @phpstan-ignore-next-line Seems to be broken or missing some type */
    return $model::factory(...$args)->create($attributes);
}

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  class-string<TModel>  $model
 * @param  array  $attributes
 * @param  array  $args
 * @return TModel|\Illuminate\Database\Eloquent\Collection<int, TModel>
 */
function make(string $model, $attributes = [], ...$args)
{
    /** @phpstan-ignore-next-line Seems to be broken or missing some type */
    return $model::factory(...$args)->make($attributes);
}

function find(string $globalId): Model
{
    return Utils::resolveModelFromGlobalId($globalId);
}

function coord_distance(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
{
    $theta = $longitude1 - $longitude2;
    $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $distance = acos($distance);
    $distance = rad2deg($distance);
    $distance *= 60 * 1.1515;
    $distance *= 1.609344;

    return round($distance, 2);
}

function is_discount_active(Discount $discount): bool
{
    return ! $discount->end || now()->lt(Carbon::createFromTimestamp($discount->end));
}

function discounted_amount(float $amount, Coupon|Discount $discount, Plan $plan): float
{
    $coupon = null;
    if ($discount instanceof Discount) {
        $coupon = $discount->coupon;
    } else {
        $coupon = $discount;
        $discount = null;
    }

    if (! $discount || is_discount_active($discount)) {
        if ($coupon->amount_off) {
            if ($plan->interval === 'year' && $coupon->duration_in_months && $coupon->duration_in_months < 12) {
                return $amount - ($amount - ($coupon->amount_off / 100)) * ($coupon->duration_in_months / 12);
            }

            return $amount - ($coupon->amount_off / 100);
        }

        if ($coupon->percent_off) {
            if ($plan->interval === 'year' && $coupon->duration_in_months && $coupon->duration_in_months < 12) {
                return $amount - ($amount * ($coupon->percent_off / 100)) * ($coupon->duration_in_months / 12);
            }

            return $amount - ($amount * ($coupon->percent_off / 100));
        }
    }

    return $amount;
}

function get_base_query(Builder|RawBuilder|EloquentBuilder|Relation $builder): RawBuilder
{
    if ($builder instanceof EloquentBuilder) {
        return $builder->getQuery();
    }

    if ($builder instanceof Relation) {
        return $builder->getQuery()->getQuery();
    }

    /** @phpstan-ignore-next-line The builder should be of the correct type */
    return $builder;
}

function ilike(?Connection $connection = null): string
{
    $connection = $connection ?? DB::connection();

    return $connection->getDriverName() === 'pgsql' ? 'ilike' : 'like';
}

function remove_special_chars(string $string): string
{
    /** @var string $sanitizedString */
    $sanitizedString = preg_replace('/\W+/', ' ', $string);

    return trim($sanitizedString);
}

function can_edit_resources_database(): bool
{
    return config('database.connections.resources.username') === 'hylark';
}

function should_be_scoped(Model $model): bool
{
    return ! is_a($model, Tenant::class)
    && ! is_a($model, NotScoped::class)
    && ! in_array($model::class, config('tenancy.not_scoped_models', []), true);
}
