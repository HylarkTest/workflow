<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Pin;
use App\Models\Base;
use App\Models\Item;
use App\Models\Link;
use App\Models\Note;
use App\Models\Page;
use App\Models\Todo;
use App\Models\User;
use App\Models\Drive;
use App\Models\Event;
use App\Models\Image;
use App\Models\Space;
use App\Models\Marker;
use App\Models\Mapping;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\LinkList;
use App\Models\Notebook;
use App\Models\Pinboard;
use App\Models\TodoList;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Core\Pages\PageType;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use Laravel\Cashier\Cashier;
use App\Models\BaseUserPivot;
use App\Models\DeadlineGroup;
use Illuminate\Mail\Mailable;
use App\Core\ActionTranslator;
use LaravelUtils\LaravelUtils;
use Nuwave\Lighthouse\GraphQL;
use Illuminate\Events\Dispatcher;
use Laravel\Cashier\Subscription;
use Mappings\Models\CategoryItem;
use Markers\Models\MarkablePivot;
use App\Models\GlobalNotification;
use Illuminate\Support\HtmlString;
use App\Core\PlanFeatureRepository;
use App\GraphQL\AST\BuildDynamicApi;
use App\Models\Support\SupportTopic;
use Illuminate\Validation\Validator;
use App\Models\Support\SupportFolder;
use Illuminate\Support\Facades\Redis;
use App\Models\Support\SupportArticle;
use Mappings\Events\RelationshipUnset;
use Nuwave\Lighthouse\Schema\RootType;
use App\Models\Support\SupportCategory;
use Illuminate\Support\ServiceProvider;
use Mappings\Events\RelationshipsAdded;
use Mappings\Events\RelationshipsRemoved;
use Illuminate\Queue\Events\JobProcessing;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Illuminate\Routing\Events\RouteMatched;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Events\StartExecution;
use Illuminate\Database\Eloquent\Relations\Relation;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use LaravelUtils\Database\Commands\SoftDeletedModelsPruneCommand;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions;
use Actions\Core\Contracts\ActionTranslator as ActionTranslatorInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlanFeatureRepository::class);
        $this->app->bind(ActionTranslatorInterface::class, ActionTranslator::class);
        $this->loadJsonTranslationsFrom(base_path('frontend/src/locales/translations'));
    }

    public function boot(Dispatcher $events): void
    {
        Relation::enforceMorphMap([
            Base::class,
            BaseUserPivot::class,
            User::class,
            Space::class,
            Page::class,
            Note::class,
            Notebook::class,
            Pin::class,
            Pinboard::class,
            Link::class,
            LinkList::class,
            Todo::class,
            TodoList::class,
            Document::class,
            Drive::class,
            Marker::class,
            MarkerGroup::class,
            Category::class,
            CategoryItem::class,
            Deadline::class,
            DeadlineGroup::class,
            Mapping::class,
            Item::class,
            Calendar::class,
            Event::class,
            Image::class,
            MarkablePivot::class,
            GlobalNotification::class,
            SupportCategory::class,
            SupportArticle::class,
            SupportFolder::class,
            SupportTopic::class,
            Subscription::class,
        ]);

        foreach ([
            Notebook::class,
            LinkList::class,
            Drive::class,
            TodoList::class,
            Calendar::class,
            Pinboard::class,
        ] as $listClass) {
            $listClass::deleted(function (
                /** @var \App\Models\Notebook|\App\Models\LinkList|\App\Models\Drive|\App\Models\TodoList|\App\Models\Calendar|\App\Models\Pinboard $list */
                $list
            ) use ($listClass) {
                $list->base->pages()
                    ->where('type', PageType::fromClass($listClass))
                    ->each(function (Page $page) use ($list) {
                        $page->lists = Arr::where($page->lists, fn (string $id) => $id !== $list->global_id);
                        $page->save();
                    });
            });
        }

        LaravelUtils::disableTimestampsForSoftDelete();

        Request::macro('forwardedHost', function () {
            /** @var $this \App\Http\Request */
            /** @phpstan-ignore-next-line Macros change the context to the macroable class */
            return $this->header('x-forwarded-host', $this->input('forwarded-host'));
        });

        Mailable::macro('viewWithTheme', function (string $view, array $data = [], string $theme = 'default') {
            $viewFactory = resolve(Factory::class);
            $viewFactory->flushFinderCache();

            $contents = $viewFactory->make($view, $data)->render();

            if ($viewFactory->exists($customTheme = Str::start($theme, 'mail.'))) {
                $theme = $customTheme;
            } else {
                $theme = str_contains($theme, '::')
                    ? $theme
                    : 'mail::themes.'.$theme;
            }

            /** @phpstan-ignore-next-line We are in the Mailable context */
            $this->html = new HtmlString((new CssToInlineStyles)->convert(
                $contents, $viewFactory->make($theme, $data)->render()
            ));

            return $this;
        });

        Redis::enableEvents();

        /*
         * Lighthouse and Octane do not play well together, especially when the
         * schema can be changed between requests. Octane and Horizon keep the
         * Lighthouse instances in memory between requests and jobs, which
         * messes things up when we rebuild the schema each time.
         *
         * At the start of each request and job we forget all the instances so
         * the schema can be recreated, and when GraphQL is executed we reset
         * the registry so GraphQL doesn't end up comparing old types to new
         * types.
         *
         * The type registry reset only needs to happen once for each request
         * and job (this is important for subscriptions as it starts execution
         * for each subscription which gets messed up if the registry is reset
         * and the Lighthouse instances aren't), so these references are in
         * place to ensure the reset only happens once.
         */
        $instanceResetReference = null;
        $registryResetReference = null;

        foreach ([RouteMatched::class, JobProcessing::class] as $event) {
            $events->listen(
                $event,
                function (RouteMatched|JobProcessing $event) use (&$instanceResetReference) {
                    if (($event instanceof JobProcessing && $event->connectionName !== 'sync')
                        || ($event instanceof RouteMatched && $event->route->named('graphql'))) {
                        $this->app->forgetInstance(GraphQL::class);
                        $this->app->forgetInstance(SchemaBuilder::class);
                        $this->app->forgetInstance(BroadcastsSubscriptions::class);
                        $instanceResetReference = new \stdClass;
                    }
                },
            );
        }

        $events->listen(
            StartExecution::class,
            function (StartExecution $event) use (&$instanceResetReference, &$registryResetReference) {
                if ($instanceResetReference) {
                    if ($registryResetReference && $registryResetReference === $instanceResetReference) {
                        return;
                    }
                    $registryResetReference = $instanceResetReference;
                }
                /** @var \App\Models\Base|null $base */
                $base = tenancy()->tenant;
                if ($base) {
                    $typeRegistry = resolve(TypeRegistry::class);
                    $typeRegistry->reset();
                    resolve(BuildDynamicApi::class)->build($base->withoutRelations());
                    // In `GraphQL\Type\Schema::getTypeMap()` the root query
                    // mutation and subscription types are set manually, so when
                    // we reset the type registry here the root types will be
                    // different and the Schema doesn't like that. So when we
                    // reset them we need to override the set root types with
                    // the new ones.
                    $config = $event->schema->getConfig();

                    /** @var \GraphQL\Type\Definition\ObjectType $query */
                    $query = $typeRegistry->get(RootType::QUERY);
                    $config->setQuery($query);

                    /** @var \GraphQL\Type\Definition\ObjectType $mutation */
                    $mutation = $typeRegistry->get(RootType::MUTATION);
                    $config->setMutation($mutation);

                    /** @var \GraphQL\Type\Definition\ObjectType $subscription */
                    $subscription = $typeRegistry->get(RootType::SUBSCRIPTION);
                    $config->setSubscription($subscription);
                }
            }
        );

        $events->listen(
            [RelationshipsAdded::class, RelationshipsRemoved::class, RelationshipUnset::class],
            function (RelationshipsAdded|RelationshipsRemoved|RelationshipUnset $event) {
                /** @var \App\Models\Item $parent */
                $parent = $event->parent;
                $parent->unsetRelation('allRelatedItems')->searchable();

                /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $children */
                $children = $event instanceof RelationshipUnset
                    ? (new Item)->newCollection([$event->child])
                    : $event->children;

                $children->map->unsetRelation('allInverseRelatedItems')->searchable();
            }
        );

        ValidatorFacade::extend('not_in_array', static function (string $attribute, $value, array $parameters, Validator $validator): bool {
            $validator->requireParameterCount(1, $parameters, 'not_in_array');

            return ! $validator->validateInArray($attribute, $value, $parameters);
        });

        ConvertEmptyStringsToNull::skipWhen(function (Request $request) {
            return $request->is('graphql')
                || $request->is('preferences');
        });

        if (app()->environment('production')) {
            Cashier::calculateTaxes();
        }

        if ($this->app->runningInConsole()) {
            $this->commands([SoftDeletedModelsPruneCommand::class]);
        }
    }
}
