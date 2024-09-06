<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use MarkupUtils\HTML;
use MarkupUtils\Markup;
use MarkupUtils\MarkupType;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Core\Mappings\Fields\Concerns\HasMaxLength;
use Mappings\Core\Mappings\Fields\Concerns\TruncatesText;
use Mappings\Core\Mappings\Fields\Contracts\StringableField;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class FormattedField extends Field implements StringableField
{
    use HasMaxLength {
        HasMaxLength::fieldValueRules as traitRules;
    }
    use TruncatesText {
        TruncatesText::arguments as truncateArguments;
    }

    public const MAX_LENGTH = 5_000;

    public static string $type = 'FORMATTED';

    public string $graphQLType = 'String';

    public string $graphQLInputType = 'String';

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = $this->traitRules($isCreate);

        if ($maxText = $this->rule('maxText', false)) {
            $rules[] = "max_strip_format:$maxText";
        }

        return $rules;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(
            static::maxOptionRules(),
            parent::optionRules($data),
            [
                'rules.maxText' => ['integer', 'lte_default:field.options.rules.max,'.self::MAX_LENGTH],
            ]
        );
    }

    /**
     * @param  string  $value
     * @return array|string|null
     */
    public function resolveSingleValue($value, array $args)
    {
        if (! $value) {
            return $value;
        }
        $markup = $this->getMarkup($value);

        if ($markup instanceof HTML) {
            $markup->clean();
        }

        if (isset($args['truncate']) || isset($args['plaintext'])) {
            return $this->truncateValue(
                (string) $markup->convertToPlaintext(),
                $args,
            );
        }

        return $markup->__toString();
    }

    protected function getFormat(): MarkupType
    {
        return config('mappings.fields.formatted.format');
    }

    protected function getMarkup(string $value): Markup
    {
        return $this->getFormat()->createMarkup($value);
    }

    protected function arguments(): ?array
    {
        return array_merge(
            $this->truncateArguments() ?: [],
            ['plaintext' => $this->boolean(nullable: true)]
        );
    }
}
