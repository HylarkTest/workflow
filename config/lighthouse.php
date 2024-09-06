<?php

declare(strict_types=1);

use GraphQL\Error\DebugFlag;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\DisableIntrospection;
use Nuwave\Lighthouse\Http\Middleware\AcceptJson;
use Nuwave\Lighthouse\Validation\ValidateDirective;
use Nuwave\Lighthouse\Schema\Directives\TrimDirective;
use Nuwave\Lighthouse\Subscriptions\SubscriptionRouter;
use Nuwave\Lighthouse\Schema\Directives\SpreadDirective;
use Nuwave\Lighthouse\Schema\Directives\SanitizeDirective;
use Nuwave\Lighthouse\Schema\Directives\RenameArgsDirective;
use Nuwave\Lighthouse\Schema\Directives\TransformArgsDirective;

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the HTTP route that your GraphQL server responds to.
    | You may set `route` => false, to disable the default route
    | registration and take full control.
    |
    */

    'route' => [
        /*
         * The URI the endpoint responds to, e.g. domain.com/graphql.
         */
        'uri' => '/graphql',

        /*
         * Lighthouse creates a named route for convenient URL generation and redirects.
         */
        'name' => 'graphql',

        /*
         * Beware that middleware defined here runs before the GraphQL execution phase,
         * make sure to return spec-compliant responses in case an error is thrown.
         */
        'middleware' => [
            AcceptJson::class,
            'api',
            'auth:web,api',
            'tenancy',

            // Logs every incoming GraphQL query.
            // \Nuwave\Lighthouse\Support\Http\Middleware\LogGraphQLQueries::class,
        ],

        /*
         * The `prefix` and `domain` configuration options are optional.
         */
        // 'prefix' => '',
        // 'domain' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The guard to use for authenticating GraphQL requests, if needed.
    | This setting is used whenever Lighthouse looks for an authenticated user, for example in directives
    | such as `@guard` and when applying the `AttemptAuthentication` middleware.
    |
    */

    'guard' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Schema Location
    |--------------------------------------------------------------------------
    |
    | Path to your .graphql schema file.
    | Additional schema files may be imported from within that file.
    |
    */

    'schema' => [
        'register' => base_path('graphql/schema.graphql'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Cache
    |--------------------------------------------------------------------------
    |
    | A large part of schema generation consists of parsing and AST manipulation.
    | This operation is very expensive, so it is highly recommended to enable
    | caching of the final schema to optimize performance of large schemas.
    |
    */

    'schema_cache' => [
        /*
         * Setting to true enables schema caching.
         */
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', true),

        /*
         * File path to store the lighthouse schema.
         * Only relevant if version is set to 2.
         */
        'path' => env('LIGHTHOUSE_CACHE_PATH', base_path('bootstrap/cache/lighthouse-schema.php')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | These are the default namespaces where Lighthouse looks for classes to
    | extend functionality of the schema. You may pass in either a string
    | or an array, they are tried in order and the first match is used.
    |
    */

    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Queries',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives', 'App\\GraphQL\\Directives\\Manager'],
        'validators' => ['App\\GraphQL\\Validators'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Control how Lighthouse handles security related query validation.
    | Read more at http://webonyx.github.io/graphql-php/security/
    |
    */

    'security' => [
        'max_query_complexity' => QueryComplexity::DISABLED,
        'max_query_depth' => QueryDepth::DISABLED,
        'disable_introspection' => DisableIntrospection::DISABLED,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Set defaults for the pagination features within Lighthouse, such as
    | the @paginate directive, or paginated relation directives.
    |
    */

    'pagination' => [
        /*
         * Allow clients to query paginated lists without specifying the amount of items.
         * Setting this to `null` means clients have to explicitly ask for the count.
         */
        'default_count' => null,

        /*
         * Limit the maximum amount of items that clients can request from paginated lists.
         * Setting this to `null` means the count is unrestricted.
         */
        'max_count' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Control the debug level as described in https://webonyx.github.io/graphql-php/error-handling/
    | Debugging is only applied if the global Laravel debug config is set to true.
    |
    | When you set this value through an environment variable, use the following reference table:
    |  0 => INCLUDE_NONE
    |  1 => INCLUDE_DEBUG_MESSAGE
    |  2 => INCLUDE_TRACE
    |  3 => INCLUDE_TRACE | INCLUDE_DEBUG_MESSAGE
    |  4 => RETHROW_INTERNAL_EXCEPTIONS
    |  5 => RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_DEBUG_MESSAGE
    |  6 => RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_TRACE
    |  7 => RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_TRACE | INCLUDE_DEBUG_MESSAGE
    |  8 => RETHROW_UNSAFE_EXCEPTIONS
    |  9 => RETHROW_UNSAFE_EXCEPTIONS | INCLUDE_DEBUG_MESSAGE
    | 10 => RETHROW_UNSAFE_EXCEPTIONS | INCLUDE_TRACE
    | 11 => RETHROW_UNSAFE_EXCEPTIONS | INCLUDE_TRACE | INCLUDE_DEBUG_MESSAGE
    | 12 => RETHROW_UNSAFE_EXCEPTIONS | RETHROW_INTERNAL_EXCEPTIONS
    | 13 => RETHROW_UNSAFE_EXCEPTIONS | RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_DEBUG_MESSAGE
    | 14 => RETHROW_UNSAFE_EXCEPTIONS | RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_TRACE
    | 15 => RETHROW_UNSAFE_EXCEPTIONS | RETHROW_INTERNAL_EXCEPTIONS | INCLUDE_TRACE | INCLUDE_DEBUG_MESSAGE
    |
    */

    'debug' => env('LIGHTHOUSE_DEBUG', DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE),

    /*
    |--------------------------------------------------------------------------
    | Error Handlers
    |--------------------------------------------------------------------------
    |
    | Register error handlers that receive the Errors that occur during execution
    | and handle them. You may use this to log, filter or format the errors.
    | The classes must implement \Nuwave\Lighthouse\Execution\ErrorHandler
    |
    */

    'error_handlers' => [
        \Nuwave\Lighthouse\Execution\AuthenticationErrorHandler::class,
        \App\GraphQL\ErrorHandlers\AuthorizationErrorHandler::class,
        \LighthouseHelpers\Exceptions\ValidationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\ReportingErrorHandler::class,
        \App\GraphQL\ErrorHandlers\MissingErrorHandler::class,
        \App\GraphQL\ErrorHandlers\InvalidGrantErrorHandler::class,
        \LighthouseHelpers\Exceptions\CoercionErrorHandler::class,
        //        \Nuwave\Lighthouse\Testing\RethrowingErrorHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Middleware
    |--------------------------------------------------------------------------
    |
    | Register global field middleware directives that wrap around every field.
    | Execution happens in the defined order, before other field middleware.
    | The classes must implement \Nuwave\Lighthouse\Support\Contracts\FieldMiddleware
    |
    */

    'field_middleware' => [
        TrimDirective::class,
        SanitizeDirective::class,
        ValidateDirective::class,
        TransformArgsDirective::class,
        SpreadDirective::class,
        RenameArgsDirective::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global ID
    |--------------------------------------------------------------------------
    |
    | The name that is used for the global id field on the Node interface.
    | When creating a Relay compliant server, this must be named "id".
    |
    */

    'global_id_field' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Batched Queries
    |--------------------------------------------------------------------------
    |
    | GraphQL query batching means sending multiple queries to the server in one request,
    | You may set this flag to either process or deny batched queries.
    |
    */

    'batched_queries' => true,

    /*
    |--------------------------------------------------------------------------
    | Transactional Mutations
    |--------------------------------------------------------------------------
    |
    | If set to true, mutations such as @create or @update will be
    | wrapped in a transaction to ensure atomicity.
    |
    */

    'transactional_mutations' => true,

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment Protection
    |--------------------------------------------------------------------------
    |
    | If set to true, mutations will use forceFill() over fill() when populating
    | a model with arguments in mutation directives. Since GraphQL constrains
    | allowed inputs by design, mass assignment protection is not needed.
    |
    | Will default to true in v5.
    |
    */

    'force_fill' => false,

    /*
    |--------------------------------------------------------------------------
    | Batchload Relations
    |--------------------------------------------------------------------------
    |
    | If set to true, relations marked with directives like @hasMany or @belongsTo
    | will be optimized by combining the queries through the BatchLoader.
    |
    */

    'batchload_relations' => true,

    /*
    |--------------------------------------------------------------------------
    | GraphQL Subscriptions
    |--------------------------------------------------------------------------
    |
    | Here you can define GraphQL subscription broadcaster and storage drivers
    | as well their required configuration options.
    |
    */

    'subscriptions' => [
        /*
         * Determines if broadcasts should be queued by default.
         */
        'queue_broadcasts' => env('LIGHTHOUSE_QUEUE_BROADCASTS', true),

        /*
         * Determines the queue to use for broadcasting queue jobs.
         */
        'broadcasts_queue_name' => env('LIGHTHOUSE_BROADCASTS_QUEUE_NAME', 'pusher'),

        /*
         * Default subscription storage.
         *
         * Any Laravel supported cache driver options are available here.
         */
        'storage' => env('LIGHTHOUSE_SUBSCRIPTION_STORAGE', 'redis'),

        /*
         * Default subscription storage time to live in seconds.
         *
         * Indicates how long a subscription can be active before it's automatically removed from storage.
         * Setting this to `null` means the subscriptions are stored forever. This may cause
         * stale subscriptions to linger indefinitely in case cleanup fails for any reason.
         */
        'storage_ttl' => env('LIGHTHOUSE_SUBSCRIPTION_STORAGE_TTL', 3600),

        /*
         * Default subscription broadcaster.
         */
        'broadcaster' => env('LIGHTHOUSE_BROADCASTER', 'pusher'),

        /*
         * Subscription broadcasting drivers with config options.
         */
        'broadcasters' => [
            'log' => [
                'driver' => 'log',
            ],
            'pusher' => [
                'driver' => 'pusher',
                'routes' => SubscriptionRouter::class.'@pusher',
                'connection' => 'pusher',
            ],
            'echo' => [
                'driver' => 'echo',
                'connection' => env('LIGHTHOUSE_SUBSCRIPTION_REDIS_CONNECTION', 'default'),
                'routes' => SubscriptionRouter::class.'@echoRoutes',
            ],
        ],

        /*
         * Controls the format of the extensions response.
         * Allowed values: 1, 2
         */
        'version' => env('LIGHTHOUSE_SUBSCRIPTION_VERSION', 2),

        /*
         * Should the subscriptions extension be excluded when the response has no subscription channel?
         * This optimizes performance by sending less data, but clients must anticipate this appropriately.
         * Will default to true in v6 and be removed in v7.
         */
        'exclude_empty' => env('LIGHTHOUSE_SUBSCRIPTION_EXCLUDE_EMPTY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Defer
    |--------------------------------------------------------------------------
    |
    | Configuration for the experimental @defer directive support.
    |
    */

    'defer' => [
        /*
         * Maximum number of nested fields that can be deferred in one query.
         * Once reached, remaining fields will be resolved synchronously.
         * 0 means unlimited.
         */
        'max_nested_fields' => 0,

        /*
         * Maximum execution time for deferred queries in milliseconds.
         * Once reached, remaining fields will be resolved synchronously.
         * 0 means unlimited.
         */
        'max_execution_ms' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Apollo Federation
    |--------------------------------------------------------------------------
    |
    | Lighthouse can act as a federated service: https://www.apollographql.com/docs/federation/federation-spec.
    |
    */

    'federation' => [
        /*
         * Location of resolver classes when resolving the `_entities` field.
         */
        'entities_resolver_namespace' => 'App\\GraphQL\\Entities',
    ],
];
