<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Concerns;

/**
 * Trait HasMaxLength
 *
 * @mixin \Mappings\Core\Mappings\Fields\Field
 */
trait TruncatesText
{
    protected function truncateValue(string|array|null $value, array $args): string|array|null
    {
        if ($value && isset($args['truncate'])) {
            $str = \is_array($value) ? $value['value'] : $value;
            $originalLength = mb_strlen($str);

            $str = trim(mb_substr($str, 0, $args['truncate']));

            if (mb_strlen($str) < $originalLength) {
                $str .= $args['suffix'];
            }
            if (\is_array($value)) {
                $value['value'] = $str;
            } else {
                $value = $str;
            }
        }

        return $value;
    }

    protected function arguments(): ?array
    {
        return [
            'truncate' => $this->int(nullable: true),
            'suffix' => $this->string(nullable: true, default: '...'),
        ];
    }
}
