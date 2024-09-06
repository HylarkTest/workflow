<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use GraphQL\Deferred;
use Mappings\Models\Document;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Documents\Contracts\DocumentRepository;

/**
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class FileField extends Field
{
    public const MAX_SIZE = 2000;

    public static string $type = 'FILE';

    public string $graphQLType = 'File';

    public string $graphQLInputType = 'Upload';

    protected DocumentRepository $documents;

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, DocumentRepository $documents)
    {
        if ($extensions = $field['options']['rules']['extensions'] ?? false) {
            $field['options']['rules']['extensions'] = array_map(static function ($extension) {
                return mb_strtolower(trim($extension, '.'));
            }, $extensions);
        }

        parent::__construct($field, $translator);
        $this->documents = $documents;
    }

    /**
     * @return ValidationRule[]
     */
    public function fieldValueRules(bool $isCreate): array
    {
        $rules = parent::fieldValueRules($isCreate);

        $rules[] = 'file';

        $max = static::MAX_SIZE;

        if ($customMax = $this->rule('max')) {
            $max = min($customMax, $max);
        }

        $rules[] = "max:$max";

        /** @var null|string[] $extensions */
        $extensions = $this->rule('extensions');
        if ($extensions) {
            $rules[] = ['mimes', ...$extensions];
        }

        return $rules;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(parent::optionRules($data), [
            'rules.max' => ['integer', ['max', static::MAX_SIZE]],
            'rules.extensions' => ['array'],
        ]);
    }

    public function resolveSingleValue($value, array $args): array|Deferred
    {
        $isUrl = \is_string($value);

        if ($isUrl) {
            return [
                'filename' => $value,
                'url' => $value,
                'size' => null,
                'extension' => null,
            ];
        }

        return $this->documents->batchLoad($value, function (?Document $document) {
            if (! $document) {
                return null;
            }

            return [
                'filename' => $document->filename(),
                'url' => $document->url(),
                'size' => $document->size(),
                'extension' => $document->extension(),
            ];
        });
    }

    public function canBeSorted(): bool
    {
        return false;
    }

    /**
     * @param  mixed  $item
     * @param  mixed|null  $originalValue
     * @return array|int
     */
    public function prepareForSerialization($item, $originalValue = null)
    {
        if ($originalValue && ! $this->documents->remove($originalValue)) {
            throw new \RuntimeException('Unable to delete original files');
        }

        return $this->documents->store($item)->id();
    }
}
