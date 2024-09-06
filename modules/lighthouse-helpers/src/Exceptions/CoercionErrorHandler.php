<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use GraphQL\Error\Error;
use GraphQL\Utils\Utils;
use Illuminate\Support\Str;
use GraphQL\Error\CoercionError;
use Mappings\Core\Mappings\Fields\FieldType;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Mappings\Core\Mappings\Fields\FieldCollection;

class CoercionErrorHandler implements ErrorHandler
{
    public function __invoke(?Error $error, \Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof CoercionError && app()->environment('production')) {
            // We will try to obfuscate the invalid value, but if anything goes
            // wrong, we will just return the original error.
            try {
                $message = $underlyingException->getMessage();
                $mapping = null;
                if (Str::endsWith($message, 'ItemDataInput".')) {
                    preg_match('/"([a-zA-Z0-9_-]+)ItemDataInput".$/', $message, $matches);
                    $mappingName = lcfirst($matches[1]);
                    $mapping = tenant()->mappings()->where('api_singular_name', $mappingName)->first();
                }

                $invalidValue = $underlyingException->invalidValue;
                $obfuscatedData = $this->obfuscate($invalidValue ?? [], $mapping?->fields);

                return $next(new Error(
                    str_replace(
                        $underlyingException->printInvalidValue(),
                        Utils::printSafeJson($obfuscatedData),
                        $error->getMessage(),
                    ),
                    $error->getNodes(),
                    $error->getSource(),
                    $error->getPositions(),
                    $error->getPath(),
                    $underlyingException,
                ));
            } catch (\Throwable $e) {
                report($e);

                return $next($error);
            }
        }

        return $next($error);
    }

    protected function obfuscate(mixed $data, ?FieldCollection $fields = null): string|array|null
    {
        if (is_array($data)) {
            $obfuscatedData = [];
            foreach ($data as $key => $value) {
                $field = \is_string($key) ? $fields?->firstWhere('apiName', $key) : null;
                if ($field) {
                    $key = $field->id();
                }
                $multiFields = null;
                if ($field?->type()->is(FieldType::MULTI())) {
                    /** @var \Mappings\Core\Mappings\Fields\Types\MultiField $field */
                    $multiFields = $field->fields();
                }
                $obfuscatedData[$key] = $this->obfuscate($value, $multiFields);
            }

            return $obfuscatedData;
        }

        return match (true) {
            \is_string($data) => '[string of length: '.mb_strlen($data).']',
            \is_bool($data) => '[boolean]',
            \is_int($data) => '[integer]',
            \is_float($data) => '[float]',
            default => $data,
        };
    }
}
