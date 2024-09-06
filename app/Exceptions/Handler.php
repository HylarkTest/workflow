<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Spiral\Goridge\Exception\HeaderException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use AccountIntegrations\Exceptions\InvalidGrantException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        InvalidGrantException::class, // This is handled by the InvalidGrantErrorHandler
        HeaderException::class, // This is thrown by Octane and doesn't seem to have any effect
    ];

    /**
     * Register the exception handling callbacks for the application
     */
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
            if ($e instanceof FatalError && Str::contains($e->getMessage(), 'Broken pipe in')) {
                return;
            }
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        $this->map(QueryException::class, function (QueryException $e) {
            if (
                $e->getConnectionName() === 'resources'
                && Str::contains($e->getMessage(), 'Connection timed out')
                && app()->environment('local')
            ) {
                return new \Exception(
                    'You are trying to access the resources database without the VPN. Please connect to the VPN or change the database connection in your .env file.',
                    0,
                    $e
                );
            }

            return $e;
        });
    }

    /**
     * Render the given HttpException.
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderHttpException(HttpExceptionInterface $e)
    {
        if ($e instanceof InvalidSignatureException && request()->routeIs('verification.verify')) {
            return redirect('/activate');
        }

        $this->registerErrorViewPaths();

        if ($view = $this->getHttpExceptionView($e)) {
            try {
                return response()->view(
                    $view,
                    $this->getErrorPageParameters($e),
                    $e->getStatusCode(),
                    $e->getHeaders()
                );
            } catch (\Throwable $t) {
                config('app.debug') && throw $t;

                $this->report($t);
            }
        }

        return $this->convertExceptionToResponse($e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest($exception->redirectTo() ?? '/login');
    }

    /**
     * Register the error template hint paths.
     *
     * @return void
     */
    protected function registerErrorViewPaths()
    {
        /** @var array<string> $viewPaths */
        $viewPaths = config('view.paths');

        View::replaceNamespace('errors', collect($viewPaths)->map(function (string $path) {
            return "{$path}/errors";
        })->all());
    }

    /**
     * Get the default parameters for the error page view
     */
    private function getErrorPageParameters(HttpExceptionInterface $e): array
    {
        $parameters = [];
        $statusCode = $e->getStatusCode();

        foreach ([
            'code' => $e->getStatusCode(),
            'message' => $e->getMessage(),
            'explanation' => null,
            'imgAltText' => null,
        ] as $key => $preferredValue) {
            $frontendTranslationString = "*.errors.$statusCode.$key";
            $frontendValue = __($frontendTranslationString);

            if ($e instanceof ClientAwareHttpException) {
                if ($key === 'message') {
                    $parameters['message'] = $e->getMessage();

                    continue;
                }
                if ($key === 'explanation') {
                    $parameters['explanation'] = $e->getExplanation() ?? $frontendValue;

                    continue;
                }
            }

            if ($frontendValue !== $frontendTranslationString) {
                $parameters[$key] = $frontendValue;

                continue;
            }

            $fallbackValue = Lang::has("errors.error_pages.$statusCode.$key")
                ? __("errors.error_pages.$statusCode.$key")
                : __("errors.error_pages.default.$key");
            $parameters[$key] = $preferredValue ?? $fallbackValue;
        }

        $parameters['image'] = config("errors.images.$statusCode") ?? config('errors.images.default');

        return $parameters;
    }
}
