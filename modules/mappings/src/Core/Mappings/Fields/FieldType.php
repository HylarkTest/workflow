<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

use LaravelUtils\Enums\ExtendableEnum;

/**
 * Class FieldType
 *
 * @method static FieldType ADDRESS();
 * @method static FieldType BOOLEAN();
 * @method static FieldType CATEGORY();
 * @method static FieldType CURRENCY();
 * @method static FieldType DATE();
 * @method static FieldType DATE_TIME();
 * @method static FieldType DURATION();
 * @method static FieldType EMAIL();
 * @method static FieldType FILE();
 * @method static FieldType FORMATTED();
 * @method static FieldType ICON();
 * @method static FieldType IMAGE();
 * @method static FieldType INTEGER();
 * @method static FieldType LINE();
 * @method static FieldType LOCATION();
 * @method static FieldType MONEY();
 * @method static FieldType MULTI();
 * @method static FieldType NAME();
 * @method static FieldType NUMBER();
 * @method static FieldType PARAGRAPH();
 * @method static FieldType PERCENTAGE();
 * @method static FieldType PHONE();
 * @method static FieldType RATING();
 * @method static FieldType SALARY();
 * @method static FieldType SELECT();
 * @method static FieldType SYSTEM_NAME();
 * @method static FieldType TIME();
 * @method static FieldType TITLE();
 * @method static FieldType URL();
 * @method static FieldType VOTE();
 */
class FieldType extends ExtendableEnum
{
    /**
     * @var array<class-string<\Mappings\Core\Mappings\Fields\Field>>
     */
    protected static array $classMap = [];

    public function newField(array $field): Field
    {
        return resolve(static::fieldClass($this), ['field' => $field]);
    }

    /**
     * @return class-string<\Mappings\Core\Mappings\Fields\Field>
     */
    public static function fieldClass(self $type): string
    {
        return static::$classMap[$type->key];
    }

    /**
     * @param  class-string<\Mappings\Core\Mappings\Fields\Field>|array<class-string<\Mappings\Core\Mappings\Fields\Field>>  $fields
     */
    public static function registerFields($fields): void
    {
        $fields = \is_array($fields) ? $fields : \func_get_args();

        /*
         * Make sure this class has been initialized in the cache.
         */
        static::getConstants();
        $class = static::class;

        foreach ($fields as $field) {
            $enum = $field::enum();

            static::$classMap[$enum] = $field;
            static::$constCacheArray[$class][$enum] = $enum;
        }
    }
}
