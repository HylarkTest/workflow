<?php

declare(strict_types=1);

namespace LighthouseHelpers\Scalars;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class JSON extends ScalarType
{
    /**
     * Serialize an internal value, ensuring it is a valid array map.
     *
     * @param  mixed  $value
     * @return array|\stdClass|string
     *
     * @throws \GraphQL\Error\Error
     */
    public function serialize($value)
    {
        $value = $this->validateMap($value);

        return $value;
    }

    /**
     * Parse a externally provided variable value into a Map instance.
     *
     * @param  mixed  $value
     * @return array|\stdClass|string
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseValue($value)
    {
        $value = $this->validateMap($value);

        return $value;
    }

    /**
     * Parse a literal provided as part of a GraphQL query string into a map
     *
     * @param  \GraphQL\Language\AST\StringValueNode  $valueNode
     * @param  mixed[]|null  $variables
     * @return array|\stdClass|string
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        return $this->validateMap($valueNode->value);
    }

    /**
     * @param  string|array  $value
     * @return array|\stdClass|string
     *
     * @throws \GraphQL\Error\Error
     */
    protected function validateMap($value)
    {
        if (\is_string($value)) {
            $value = json_decode($value);
            if (json_last_error()) {
                throw new Error('The map field must be an array or a json string');
            }
        }

        if ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        }
        if ($value instanceof Jsonable) {
            $value = json_decode($value->toJson(), true);
        }
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        return $value;
    }
}
