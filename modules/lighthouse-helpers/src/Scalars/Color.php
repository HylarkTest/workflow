<?php

declare(strict_types=1);

namespace LighthouseHelpers\Scalars;

use GraphQL\Error\Error;
use Color\Color as CoreColor;
use GraphQL\Type\Definition\ScalarType;

class Color extends ScalarType
{
    /**
     * Serialize an internal value, ensuring it is a valid color.
     *
     * @param  mixed  $value
     *
     * @throws \GraphQL\Error\Error
     */
    public function serialize($value): string
    {
        if ($value instanceof CoreColor) {
            return (string) $value;
        }
        $this->validateColor($value);

        return (string) CoreColor::make($value)->toHex();
    }

    /**
     * Parse a externally provided variable value into a color.
     *
     * @param  mixed  $value
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseValue($value): string
    {
        $this->validateColor($value);

        return (string) CoreColor::make($value);
    }

    /**
     * Parse a literal provided as part of a GraphQL query string into a color.
     *
     * @param  \GraphQL\Language\AST\StringValueNode  $valueNode
     * @param  mixed[]|null  $variables
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseLiteral($valueNode, ?array $variables = null): string
    {
        $this->validateColor($valueNode->value);

        return (string) CoreColor::make($valueNode->value);
    }

    /**
     * @param  mixed  $value
     *
     * @throws \GraphQL\Error\Error
     */
    protected function validateColor($value): string
    {
        if (CoreColor::isValidColor($value)) {
            return $value;
        }
        throw new Error('The color must use a valid color format (#FFFFFF, rgb(255, 255, 255), hsl(360, 0%, 100%))');
    }
}
