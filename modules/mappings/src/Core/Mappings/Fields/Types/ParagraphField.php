<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Concerns\HasMaxLength;
use Mappings\Core\Mappings\Fields\Concerns\TruncatesText;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;

class ParagraphField extends Field implements StringableField
{
    use HasMaxLength;
    use TruncatesText;

    public const MAX_LENGTH = 2000;

    public static string $type = 'PARAGRAPH';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @param  mixed  $value
     * @return mixed|string
     */
    public function resolveSingleValue($value, array $args)
    {
        return $this->truncateValue($value, $args);
    }
}
