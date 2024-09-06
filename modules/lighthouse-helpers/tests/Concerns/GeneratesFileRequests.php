<?php

declare(strict_types=1);

namespace Tests\LighthouseHelpers\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;

/**
 * Class GeneratesFileRequests
 *
 * @mixin \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests
 */
trait GeneratesFileRequests
{
    /**
     * @param  mixed  $url
     * @param  mixed  $body
     *
     * @throws \JsonException
     */
    public function convertToFileRequest($url, $body): TestResponse
    {
        $variables = Arr::dot(Arr::only($body, 'variables'));

        $files = array_filter($variables, static fn ($field) => $field instanceof UploadedFile);

        if (! $files) {
            return $this->postJson($url, $body);
        }

        foreach ($files as $key => $ignore) {
            Arr::set($body, $key, null);
        }

        return $this->call(
            'POST',
            $url,
            [
                'operations' => json_encode($body, \JSON_THROW_ON_ERROR),
                'map' => json_encode((object) array_map(static fn ($key) => [$key], array_keys($files)), \JSON_THROW_ON_ERROR),
            ],
            [],
            array_values($files),
            $this->transformHeadersToServerVars([
                'Content-Type' => 'multipart/form-data',
            ])
        );
    }
}
