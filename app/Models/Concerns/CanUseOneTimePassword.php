<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Notifications\Auth\OneTimePasswordNotification;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \Illuminate\Notifications\Notifiable
 */
trait CanUseOneTimePassword
{
    public function sendOneTimePassword(Request $request, null|int|\DateInterval|Carbon $timeout, ?string $emailAddress = null): string
    {
        $otp = sprintf('%06d', random_int(1, 999999));

        cache()->set($this->getOtpCacheKey(), $otp, $timeout);

        $this->notifyNow(new OneTimePasswordNotification($otp, $this, $request, $emailAddress));

        return $otp;
    }

    public function forgetOneTimePassword(): void
    {
        cache()->forget($this->getOtpCacheKey());
    }

    public function verifyOneTimePassword(string $password): bool
    {
        $otp = cache()->get($this->getOtpCacheKey());

        return $otp && $otp === $password;
    }

    public function hasOneTimePassword(): bool
    {
        return cache()->has($this->getOtpCacheKey());
    }

    protected function getOtpCacheKey(): string
    {
        return 'one_time_password:'.$this->getKey();
    }
}
