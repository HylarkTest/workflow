<?php

declare(strict_types=1);

namespace LaravelUtils\Enums;

use BenSampo\Enum\Enum;

class ExtendableEnum extends Enum
{
    /**
     * Constants cache.
     */
    protected static array $constCacheArray = [];

    /**
     * Merge all the values of one enum into this one.
     *
     * @param  \BenSampo\Enum\Enum<string>|class-string<\BenSampo\Enum\Enum<string>>  $enum
     */
    public static function mergeEnum(Enum|string $enum): void
    {
        /*
         * Make sure this class has been initialized in the cache.
         */
        static::getConstants();
        $class = static::class;
        static::$constCacheArray[$class] = array_merge(static::$constCacheArray[$class], $enum::getConstants());

        foreach ($enum::getConstants() as $constant) {
            $enum::macro($constant, static function () use ($constant) {
                $enumValue = self::getValue($constant);

                return new self($enumValue);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function addValues(array $values): void
    {
        /*
         * Make sure this class has been initialized in the cache.
         */
        static::getConstants();
        $class = static::class;

        foreach ($values as $enum => $value) {
            static::$constCacheArray[$class][$enum] = $value;
        }
    }

    public static function getConstants(): array
    {
        $calledClass = static::class;

        if (! \array_key_exists($calledClass, static::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            static::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return static::$constCacheArray[$calledClass];
    }

    protected static function getAttributeDescription($value): ?string
    {
        return null;
    }
}
