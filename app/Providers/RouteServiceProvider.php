<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->setupDummyMailRoute();

        $this->routes(function () {
            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            /** @var \App\Models\User|null $user */
            $user = $request->user();

            return Limit::perMinute(600)->by((string) $user?->id ?: (string) Arr::last($request->ips()));
        });
    }

    protected function setupDummyMailRoute(): void
    {
        if (app()->environment('local')) {
            Route::get('dummy-mail', function (Request $request) {
                $view = $request->query('view', 'dummy-mail');
                /** @var string|null $theme */
                $theme = $request->query('theme', config('mail.markdown.theme'));
                $mailable = new class($theme) extends Mailable
                {
                    public function __construct(?string $theme)
                    {
                        $this->theme = $theme;
                    }

                    public function build(): static
                    {
                        return $this->subject('Dummy email');
                    }
                };

                /** @phpstan-ignore-next-line For testing */
                $mailable->markdown("emails.$view");

                /** @var array $data */
                $data = $request->query() ?? [];

                foreach ($data as $key => $value) {
                    if (str_starts_with($value, '!')) {
                        $value = mb_substr($value, 1);
                        try {
                            $date = Carbon::parse($value);
                            $data[$key] = $date;
                        } catch (InvalidFormatException) {
                            // Just ignore it if it isn't a date
                        }
                    }
                }

                $mailable->with($data);

                if ($request->has('send')) {
                    /** @var string $address */
                    $address = $request->query('send');
                    if (! $address) {
                        throw new \Exception('You must specify a valid email address you own');
                    }
                    Mail::send($mailable->to($address));

                    return 'Mail has been sent';
                }

                return $mailable;
            });
        }
    }
}
