<?php

declare(strict_types=1);

namespace LighthouseHelpers\Pagination;

use Illuminate\Support\Arr;

class Cursor
{
    /**
     * Decode cursor from query arguments.
     *
     * If no 'after' argument is provided or the contents are not a valid base64 string,
     * this will return 0. That will effectively reset pagination, so the user gets the
     * first slice.
     *
     * @throws \JsonException
     */
    public static function decode(array $args): array
    {
        if ($cursor = Arr::get($args, 'after')) {
            return json_decode((string) base64_decode((string) $cursor, true), true, 512, \JSON_THROW_ON_ERROR);
        }

        return [];
    }

    /**
     * Encode the given offset to make the implementation opaque.
     */
    public static function encode(array $cursor): string
    {
        return base64_encode(json_encode($cursor) ?: '');
    }
}
