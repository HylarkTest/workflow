<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\Models\Pin;
use App\Models\Link;
use App\Models\Note;
use App\Models\Todo;
use App\Models\Event;
use App\Models\Document;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use LighthouseHelpers\Concerns\HasGlobalId;
use LighthouseHelpers\Contracts\MultipleGraphQLInterfaces;

class TypeResolver
{
    public static function resolveType(Model $rootValue, AppContext $context, ResolveInfo $resolveInfo): Type
    {
        $schema = $resolveInfo->schema;
        if ($rootValue instanceof MultipleGraphQLInterfaces) {
            $typeName = $rootValue::resolveType($rootValue);
        } elseif (\in_array(HasGlobalId::class, class_uses_recursive($rootValue), true)) {
            /** @phpstan-ignore-next-line This method is confirmed to exist by the check above */
            $typeName = $rootValue->typeName();
        } else {
            $typeName = class_basename($rootValue);
        }

        return $schema->getType($typeName) ?? throw new \Exception("Type $typeName not found in schema");
    }

    /**
     * @param  \LighthouseHelpers\Pagination\PaginationResult  $rootValue
     *
     * @throws \Exception
     */
    public static function resolveConnection($rootValue, AppContext $context, ResolveInfo $resolveInfo): Type
    {
        $typeName = match ($rootValue->baseClass ?? null) {
            Todo::class => 'TodoConnection',
            Event::class => 'EventConnection',
            Pin::class => 'PinConnection',
            Note::class => 'NoteConnection',
            Link::class => 'LinkConnection',
            Document::class => 'DocumentConnection',
            default => throw new \Exception("Type $rootValue->baseClass not found in schema"),
        };

        return $resolveInfo->schema->getType($typeName) ?? throw new \Exception("Type $typeName not found in schema");
    }
}
