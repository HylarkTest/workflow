<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers;

use GraphQL\Error\DebugFlag;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use LighthouseHelpers\Exceptions\MissingErrorHandler;
use LighthouseHelpers\Exceptions\ValidationErrorHandler;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

trait InteractsWithGraphQLExceptionHandling
{
    use MakesGraphQLRequests;

    protected function withGraphQLExceptionHandling(): static
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = app(ConfigRepository::class);
        $config->set('lighthouse.error_handlers', [
            \Nuwave\Lighthouse\Execution\AuthenticationErrorHandler::class,
            \App\GraphQL\ErrorHandlers\AuthorizationErrorHandler::class,
            ValidationErrorHandler::class,
            \Nuwave\Lighthouse\Execution\ReportingErrorHandler::class,
            MissingErrorHandler::class,
        ]);

        $config->set('lighthouse.debug', DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);

        return $this;
    }

    protected function handleGraphQLExceptions(): static
    {
        $this->withGraphQLExceptionHandling();

        return $this;
    }

    /**
     * Disable exception handling for the test.
     *
     * @return $this
     */
    protected function withoutGraphQLExceptionHandling(): static
    {
        $this->rethrowGraphQLErrors();

        return $this;
    }
}
