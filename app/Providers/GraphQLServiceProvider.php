<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Core\BaseType;
use App\Core\TaskStatus;
use App\Core\Groups\Role;
use App\GraphQL\Serializer;
use App\Core\Pages\PageType;
use Illuminate\Http\Request;
use App\GraphQL\NodeRegistry;
use Illuminate\Events\Dispatcher;
use App\GraphQL\AppContextFactory;
use Timekeeper\Core\DeadlineStatus;
use GraphQL\Type\Definition\EnumType;
use Illuminate\Support\ServiceProvider;
use App\Core\Mappings\FieldFilterOperator;
use App\GraphQL\AST\BuildCustomFieldTypes;
use Laravel\Octane\Events\RequestReceived;
use LighthouseHelpers\Core\NativeEnumType;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use App\Core\Mappings\MarkerFilterOperator;
use App\Core\Preferences\NotificationChannel;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Features\FeatureListQuery;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext;
use Nuwave\Lighthouse\Support\Contracts\SerializesContext;
use Nuwave\Lighthouse\GlobalId\NodeRegistry as BaseNodeRegistry;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CreatesContext::class, AppContextFactory::class);
        $this->app->singleton(SerializesContext::class, Serializer::class);
        $this->app->bind(BaseNodeRegistry::class, NodeRegistry::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @param  \LighthouseHelpers\Core\TypeRegistry  $typeRegistry
     */
    public function boot(TypeRegistry $typeRegistry, Dispatcher $events, BuildCustomFieldTypes $itemFieldTypesBuilder): void
    {
        $typeRegistry->register(new NativeEnumType(PageType::class));
        $typeRegistry->register(new NativeEnumType(NotificationChannel::class));
        $typeRegistry->register(new NativeEnumType(FieldFilterOperator::class));
        $typeRegistry->register(new NativeEnumType(MarkerFilterOperator::class));
        $typeRegistry->register(new NativeEnumType(DeadlineStatus::class));
        $typeRegistry->register(new NativeEnumType(Role::class));
        $typeRegistry->register(new NativeEnumType(BaseType::class));
        $typeRegistry->register(new NativeEnumType(TaskStatus::class));
        $typeRegistry->register(new EnumType([
            'name' => 'MarkableType',
            'values' => collect(MappingFeatureType::markableFeatures())
                ->mapWithKeys(fn (MappingFeatureType $type) => [$type->name => $type->value])
                ->all(),
        ]));

        $typeRegistry->register(new NativeEnumType(MappingFeatureType::class));

        /*
         * Why is this here? Well I'm glad you asked because it's so stupid!!!
         * It all starts with FormData...
         * Some requests must always use POST, even if they are updating a model.
         * If the request includes a file upload then the method must be POST
         * due to some archaic browser thing.
         *
         * If you want to use PUT and PATCH requests with FormData then you need
         * to spoof the method with a header or a _method parameter.
         * The handling of this is done in the `getMethod` method of the
         * `Symfony\Component\HttpFoundation\Request` class.
         * If you take a look at that method it checks if the static property
         * `$httpMethodParameterOverride` is set to true, and if it is then it
         * will check for the `_method` parameter and use that as the method.
         *
         * By default this property is false, but Laravel sets it to true when
         * it captures the request in the `handle` method of
         * `Illuminate\Foundation\Http\Kernel`.
         *
         * So what's the problem, eh???
         *
         * Well the problem is that Octane checks the method _before_ the request
         * is captured!!!
         * Don't believe me? Well check out
         * `Laravel\Octane\ApplicationGateway::handle`.
         * So it fetches the method without the override and that method is
         * cached on the request object. So any subsequent calls to `getMethod`
         * will return the cached value which is POST!
         *
         * So here we simply hook into the event fired by octane before it checks
         * the method and make sure the override parameter is set, so it checks
         * the appropriate method.
         */
        $events->listen(
            RequestReceived::class,
            function () {
                Request::enableHttpMethodParameterOverride();
            }
        );

        $events->listen(
            BuildSchemaString::class,
            function () {
                return collect([
                    ['drive', 'document', true],
                    ['pinboard', 'pin', true],
                    ['notebook', 'note', true],
                    ['linkList', 'link', true],
                    ['todoList', 'todo', false],
                    ['calendar', 'event', false],
                ])->map(fn ($args) => FeatureListQuery::buildSchemaForFeatureList(...$args))->implode(\PHP_EOL);
            }
        );

        $itemFieldTypesBuilder->build();
    }
}
