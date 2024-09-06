<?php

declare(strict_types=1);

namespace LighthouseHelpers;

use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;
use Nuwave\Lighthouse\Support\Utils as BaseUtils;

class Utils extends BaseUtils
{
    protected static array $classMap = [];

    protected static array $modelMap = [];

    /**
     * Try adding the default model namespace and ensure the given class is a model.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>|null
     */
    public static function namespaceModelClass(string $modelClassCandidate): ?string
    {
        if (isset(static::$classMap[$modelClassCandidate])) {
            return static::$classMap[$modelClassCandidate];
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null $className */
        $className = static::$classMap[$modelClassCandidate] = static::namespaceClassName(
            $modelClassCandidate,
            (array) config('lighthouse.namespaces.models'),
            fn (string $classCandidate) => is_subclass_of($classCandidate, Model::class)
        );

        return $className;
    }

    public static function clearCache(): void
    {
        static::$classMap = [];
        static::$modelMap = [];
    }

    public static function resolveModelFromGlobalId(string $id, ?GlobalId $globalId = null): Model
    {
        if ($model = (static::$modelMap[$id] ?? false)) {
            return $model;
        }

        $globalId = $globalId ?: Container::getInstance()->make(GlobalId::class);
        [$type, $key] = $globalId->decode($id);

        $modelClass = self::namespaceModelClass($type);

        throw_if(! $modelClass, GlobalIdException::class, 'Invalid Global ID provided');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = static::$modelMap[$id] = $modelClass::query()->findOrFail($key);

        return $model;
    }

    public static function generateRandomString(): string
    {
        $id = Str::random(5);

        if (is_numeric($id[0])) {
            $id[0] = \chr(mt_rand(97, 122));
        }

        return $id;
    }

    public static function generateGraphQLType(string $string): string
    {
        $string = preg_replace('/[^a-zA-Z0-9 ]/', '', Str::ascii($string));
        if (! $string) {
            return static::generateRandomString();
        }
        $start = ctype_alpha($string[0]) ? $string[0] : '_'.$string[0];
        $rest = preg_replace('/\W/', '_', mb_substr($string, 1, 49)) ?: '';

        return mb_strtolower($start).Str::camel($rest);
    }
}
