<?php

declare(strict_types=1);

namespace LighthouseHelpers\Core;

use Illuminate\Support\Arr;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Contracts\Validation\Factory;
use LighthouseHelpers\Exceptions\ValidationException;
use LighthouseHelpers\Concerns\BuildsGraphQLResponses;
use LighthouseHelpers\Exceptions\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Mutation
{
    use AuthorizesRequests;
    use BuildsGraphQLResponses;

    public function __construct(protected Factory $validationFactory) {}

    /**
     * @throws \Exception
     */
    protected function validate(
        array $data,
        array $rules,
        ResolveInfo $resolveInfo,
        array $messages = [],
        array $attributes = []
    ): array {
        $validator = $this->validationFactory->make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            $path = implode('.', $resolveInfo->path);

            throw new ValidationException("Validation failed for the field [$path].", $validator);
        }

        return $validator->validated();
    }

    protected function decodeId(string $id, ?string $classType = null): int
    {
        $globalId = resolve(GlobalId::class);

        [$type, $id] = $globalId->decode($id);
        if ($type !== null && $type !== $classType) {
            throw new ModelNotFoundException;
        }

        return (int) $id;
    }

    protected function decodeIds(array $ids, ?string $classType): array
    {
        return array_map(function ($id) use ($classType) {
            return $this->decodeId($id, $classType);
        }, $ids);
    }

    /**
     * @return never
     */
    protected function throwValidationException(string $field, string|array $messages): void
    {
        throw ValidationException::withMessages([$field => Arr::wrap($messages)]);
    }
}
