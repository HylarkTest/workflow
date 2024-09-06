<?php

declare(strict_types=1);

namespace LighthouseHelpers\Concerns;

trait BuildsGraphQLResponses
{
    protected function mutationResponse(int $code = 200, string $message = '', array $data = []): array
    {
        return array_merge([
            'base' => tenancy()->tenant?->getKey(),
            'code' => (string) $code,
            'success' => $code >= 200 && $code < 300,
            'message' => $message,
        ], $data);
    }
}
