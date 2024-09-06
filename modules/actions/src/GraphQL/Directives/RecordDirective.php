<?php

declare(strict_types=1);

namespace Actions\GraphQL\Directives;

use Illuminate\Database\Eloquent\Model;
use Actions\Core\Contracts\ActionRecorder;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;

class RecordDirective extends BaseDirective implements FieldMiddleware
{
    protected ActionRecorder $recorder;

    /**
     * RecordDirective constructor.
     */
    public function __construct(ActionRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Record all the actions that happen to the model during this resolution.
"""
directive @record(
  """
  Specify the class name of the model to use.
  This is only needed when the default model resolution does not work.
  """
  model: String
) on FIELD_DEFINITION
SDL;
    }

    /**
     * Wrap around the final field resolver.
     */
    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(
            fn (callable $previousResolver) => function ($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previousResolver) {
                /** @var bool $beforeUpdate */
                $beforeUpdate = true;
                $modelClass = $this->getModelClass();

                foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
                    /*
                     * The $beforeUpdate method needs to be passed by reference
                     * otherwise only the value at this point will be used and
                     * it will ignore the change to false later on.
                     */
                    $modelClass::$event(function (Model $model) use ($context, $event, &$beforeUpdate) {
                        if (! $beforeUpdate) {
                            return;
                        }

                        /** @var \Illuminate\Database\Eloquent\Model $user */
                        $user = $context->user();

                        $this->recorder->recordEvent($event, $model, $user);
                    });
                }

                $result = $previousResolver(
                    $rootValue,
                    $args,
                    $context,
                    $resolveInfo
                );

                $beforeUpdate = false;

                return $result;
            }
        );
    }
}
