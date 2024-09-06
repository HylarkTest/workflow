<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Base;
use App\Models\Item;
use App\Models\Page;
use GraphQL\Deferred;
use App\Models\Mapping;
use App\GraphQL\AppContext;
use App\Models\SavedFilter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Core\Pages\PageType;
use Illuminate\Validation\Rule;
use AccountIntegrations\Core\Scope;
use App\Models\PersonalPageSettings;
use LighthouseHelpers\Core\Mutation;
use Nuwave\Lighthouse\Support\Utils;
use Illuminate\Validation\Rules\Exists;
use App\GraphQL\Queries\Items\ItemQuery;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use App\GraphQL\Queries\Features\PinQuery;
use App\GraphQL\Queries\Features\LinkQuery;
use App\GraphQL\Queries\Features\NoteQuery;
use App\GraphQL\Queries\Features\TodoQuery;
use App\GraphQL\Queries\Features\EventQuery;
use Illuminate\Database\Eloquent\Collection;
use LighthouseHelpers\Core\ModelBatchLoader;
use App\GraphQL\Queries\Features\DocumentQuery;
use App\Core\Preferences\PersonalPagePreferences;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use App\GraphQL\Queries\Concerns\PaginatesQueries;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Execution\BatchLoader\RelationBatchLoader;
use Nuwave\Lighthouse\Execution\ModelsLoader\SimpleModelsLoader;

class PageQuery extends Mutation
{
    use PaginatesQueries;

    /**
     * @param  null  $rootValue
     * @param array{
     *     spaceId?: string,
     *     first: int,
     *     after?: string,
     * } $args
     */
    public function index($rootValue, array $args, AppContext $context): Deferred
    {
        $base = $context->base();

        $query = $base->pages();

        if (isset($args['spaceId'])) {
            $query->where('space_id', $args['spaceId']);
        }

        /** @phpstan-ignore-next-line Gives the correct type */
        return $this->paginateQuery($query, Arr::only($args, ['first', 'after']));
    }

    public function indexItems(Page $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $args['fields'] = array_map(
            fn ($filter) => [
                ...$filter,
                'match' => json_decode($filter['match'], true),
            ],
            $rootValue->fieldFilters
        );
        $args['markers'] = $rootValue->markerFilters;

        return BatchLoaderRegistry::instance(
            $resolveInfo->path,
            fn () => new RelationBatchLoader(
                new SimpleModelsLoader(
                    'mapping',
                    fn () => null
                )
            ),
        )
            ->load($rootValue)
            ->then(fn (Mapping $mapping) => Utils::constructResolver(ItemQuery::class, 'index')($mapping, $args, $context, $resolveInfo));
    }

    public function indexListItems(Page $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): SyncPromise
    {
        $args['forLists'] = $rootValue->lists ? array_map(
            fn (string $id) => resolve(GlobalId::class)->decodeID($id),
            $rootValue->lists
        ) : null;

        $query = match ($rootValue->type) {
            PageType::TODOS => TodoQuery::class,
            PageType::CALENDAR => EventQuery::class,
            PageType::DOCUMENTS => DocumentQuery::class,
            PageType::PINBOARD => PinQuery::class,
            PageType::NOTES => NoteQuery::class,
            PageType::LINKS => LinkQuery::class,
            default => throw new \Exception('Invalid page type'),
        };

        return Utils::constructResolver($query, 'index')(null, $args, $context, $resolveInfo);
    }

    /**
     * @param  null  $rootValue
     */
    public function show($rootValue, array $args, AppContext $context): Page
    {
        $base = $context->base();

        /** @var \App\Models\Page $page */
        $page = $base->pages()->findOrFail($args['id']);

        return $page;
    }

    /**
     * @param  null  $rootValue
     */
    public function storeListPage($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        if (! $base->accountLimits()->canCreatePages()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $data = Arr::only($args['input'], [
            'path',
            'folder',
            'name',
            'symbol',
            'description',
            'design',
            'type',
            'lists',
            'templateRefs',
            'image',
        ]);

        $lists = $data['lists'] ?? [];

        $this->validateLists($lists, PageType::from($data['type']), $base);

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['input']['spaceId']);

        $page = $space->pages()->make($data);
        if (\array_key_exists('image', $data)) {
            $page->updateImage($data['image']);
        }
        $page->save();

        return $this->mutationResponse(200, 'Page created successfully', [
            'page' => $page,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function storeEntitiesPage($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        if (! $base->accountLimits()->canCreatePages()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $data = Arr::only($args['input'], [
            'path',
            'folder',
            'name',
            'singularName',
            'symbol',
            'description',
            'fieldFilters',
            'markerFilters',
            'entityId',
            'newData',
            'newFields',
            'image',
        ]);

        /** @phpstan-ignore-next-line  */
        $data['type'] = $resolveInfo->argumentSet->arguments['input']->type->name === 'CreateEntitiesPageInput' ?
            PageType::ENTITIES :
            PageType::ENTITY;

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['input']['spaceId']);

        /** @var \App\Models\Mapping $mapping */
        $mapping = $space->mappings()->findOrFail($args['input']['mappingId']);

        $this->validate(
            $args,
            [
                'input.newData.fields' => ['array', Rule::in($mapping->fields->pluck('id')->all())],
                'input.newData.markers' => ['array', Rule::in($mapping->markerGroups?->pluck('id')->all() ?: [])],
            ],
            $resolveInfo,
        );

        $firstPageWithDesign = $mapping->pages->whereNotNull('design')->first();
        if ($firstPageWithDesign) {
            $data['design'] = $firstPageWithDesign->design;
        }

        /** @var \App\Models\Page $page */
        $page = $space->pages()->make($data);
        $page->mapping()->associate($mapping);

        if (\array_key_exists('image', $data)) {
            $page->updateImage($data['image']);
        }

        $page->save();

        return $this->mutationResponse(200, 'Page created successfully', [
            'page' => $page,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function updateEntitiesPage($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $id = $args['input']['id'];

        $base = $context->base();

        /** @var \App\Models\Page $page */
        $page = $base->pages()->whereIn('type', PageType::entityTypes())->findOrFail($id);
        /** @var \App\Models\Mapping $mapping */
        $mapping = $page->mapping;

        // If this query is changing a non subset page to a subset page we need to check they can do that.
        if ($page->isUnfilteredEntitiesPage() && (isset($args['input']['fieldFilters']) || isset($args['input']['markerFilters']))) {
            $hasNoOtherFullPages = $base->pages()
                ->whereKeyNot($id)
                ->where('mapping_id', $page->mapping_id)
                ->whereNull('config->markerFilters')->whereNull('config->fieldFilters')
                ->doesntExist();

            if ($hasNoOtherFullPages) {
                $this->throwValidationException('input.id', [trans('validation.custom.page.id.no_other_full_pages')]);
            }
        }

        $data = Arr::only($args['input'], [
            'path',
            'folder',
            'name',
            'singularName',
            'symbol',
            'description',
            'design',
            'fieldFilters',
            'markerFilters',
            'newData',
            'entityId',
            'image',
            'defaultFilterId',
            'personalDefaultFilterId',
        ]);

        $this->validate(
            $args,
            [
                'input.newData.fields' => ['array', Rule::in($mapping->fields->pluck('id')->all())],
                'input.newData.markers' => ['array', Rule::in($mapping->markerGroups?->pluck('id')->all() ?: [])],
                'input.defaultFilterId' => ['nullable', (new Exists('saved_filters', 'id'))->whereNull('base_user_id')],
                'input.personalDefaultFilterId' => ['nullable', new Exists('saved_filters', 'id')],
            ],
            $resolveInfo,
        );

        $viewTypes = ['TILE', 'SPREADSHEET', 'LINE', 'KANBAN'];
        $dataTypes = ['FIELDS', 'RELATIONSHIPS', 'MARKERS', 'SYSTEM', 'FEATURES', 'COLLABORATION'];
        if (isset($data['design'])) {
            $data['design'] = $this->validate(
                $args,
                [
                    'input.design.defaultView' => [
                        'nullable',
                        Rule::in([
                            ...$viewTypes,
                            /** @phpstan-ignore-next-line  */
                            ...collect($data['design']['views'] ?? [])->pluck('id')->toArray(),
                            ...$mapping->features->map->type()->map->value,
                        ]),
                        Rule::notIn($data['design']['deletedViews'] ?? []),
                    ],
                    'input.design.deletedViews.*' => [Rule::in($viewTypes)],
                    'input.design.itemDisplay' => ['array'],
                    'input.design.itemDisplay.*.header' => ['string', 'max:50'],
                    'input.design.itemDisplay.*.id' => ['required'],
                    'input.design.itemDisplay.*.fields' => ['array'],
                    'input.design.itemDisplay.*.fields.*.dataType' => [Rule::in($dataTypes)],
                    'input.design.itemDisplay.*.fields.*.formattedId' => ['required'],
                    'input.design.views' => ['array', 'max:10'],
                    'input.design.views.*.name' => ['required', 'string', 'max:50'],
                    'input.design.views.*.id' => ['required', 'string', 'max:20'],
                    'input.design.views.*.viewType' => ['required', Rule::in($viewTypes)],
                    'input.design.views.*.template' => ['string'],
                    'input.design.views.*.visibleData' => ['nullable', 'array'],
                    'input.design.views.*.visibleData.*.dataType' => ['required', Rule::in($dataTypes)],
                    'input.design.views.*.visibleData.*.slot' => ['nullable', 'string'],
                    'input.design.views.*.visibleData.*.combo' => ['nullable', 'int'],
                    'input.design.views.*.visibleData.*.designAdditional' => ['nullable'],
                    'input.design.views.*.visibleData.*.width' => ['nullable', 'int'],
                    'input.design.views.*.visibleData.*.formattedId' => ['required', 'string'],
                ],
                $resolveInfo
            )['input']['design'];
        }

        $page->fill($data);

        if ($page->type === PageType::ENTITIES) {
            $config = $page->config ?: [];
            $config = array_merge($config, Arr::only($args['input'], ['singularName']));
            $page->config = $config;
        }

        if (\array_key_exists('image', $data)) {
            $page->updateImage($data['image']);
        }

        $page->save();

        if (array_key_exists('personalDefaultFilterId', $data)) {
            $baseUser = $context->baseUser();
            $settings = $page->getPersonalSettings($baseUser);

            $settings->updatePreferences(function (PersonalPagePreferences $preferences) use ($data) {
                /** @var int|null $id */
                $id = $data['personalDefaultFilterId'];
                $preferences->personalDefaultFilterId = $id ? (int) $id : $id;
            });
        }

        return $this->mutationResponse(200, 'Page was updated successfully', [
            'page' => $page,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function updateListPage($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $data = Arr::only($args['input'], [
            'path',
            'folder',
            'name',
            'symbol',
            'description',
            'lists',
            'image',
        ]);

        $id = $args['input']['id'];

        /** @var \App\Models\Page $page */
        $page = $base->pages()->whereIn('type', PageType::listTypes())->findOrFail($id);

        $lists = $data['lists'] ?? [];

        $this->validateLists($lists, $page->type, $base);

        $page->fill($data);
        if (\array_key_exists('image', $data)) {
            $page->updateImage($data['image']);
        }

        $page->save();

        return $this->mutationResponse(200, 'Page was updated successfully', [
            'page' => $page,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $base = $context->base();

        $id = $args['input']['id'];

        /** @var \App\Models\Page $page */
        $page = $base->pages()->findOrFail($id);

        $sameMappingQuery = $base->pages()->whereKeyNot($id)->where('mapping_id', $page->mapping_id);

        $hasOtherFilteredSubsetPages = $page->isUnfilteredEntitiesPage() // First check it's a subset entity page
            && (clone $sameMappingQuery)->whereNull('config->markerFilters')->whereNull('config->fieldFilters')->doesntExist() // Then check if there are other pages with no filters
            && (clone $sameMappingQuery) // Finally check if there are pages with filters
                ->where(
                    fn ($query) => $query->whereNotNull('config->markerFilters')
                        ->orWhereNotNull('config->fieldFilters')
                )
                ->exists();

        if ($hasOtherFilteredSubsetPages) {
            $this->throwValidationException('input.id', [trans('validation.custom.page.id.subset')]);
        }

        if ($page->mapping?->pages->count() === 1) {
            $mapping = $page->mapping;
            $relatedMappings = $mapping->relationshipsWithMappings()->map->toMapping()->unique();
            $mapping->delete();
            $deleteResponse = $this->mutationResponse(200, 'Mapping was deleted successfully', [
                'mapping' => $mapping,
                'toAll' => true,
            ]);
            Subscription::broadcast('mappingDeleted', $deleteResponse);
            foreach ($relatedMappings as $relatedMapping) {
                $updateResponse = $this->mutationResponse(200, 'Mapping was deleted successfully', [
                    'mapping' => $relatedMapping,
                    'toAll' => true,
                ]);
                Subscription::broadcast('mappingUpdated', $updateResponse);
            }
            $page->deleteBy($page->mapping->pages()->getForeignKeyName());
        } else {
            $page->delete();
        }

        return $this->mutationResponse(200, 'Page was deleted successfully', [
            'page' => $page,
        ]);
    }

    public function resolveEntity(Page $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): ?Deferred
    {
        $id = $rootValue->entityId;

        return $id ? ModelBatchLoader::instanceFromModel(Item::class)->loadAndResolve($id) : null;
    }

    public function resolveDefaultFilter(Page $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): ?Deferred
    {
        $id = $rootValue->defaultFilterId;

        return $id ? ModelBatchLoader::instanceFromModel(SavedFilter::class)->loadAndResolve($id) : null;
    }

    public function resolvePersonalDefaultFilter(Page $page, array $args, AppContext $context, ResolveInfo $resolveInfo): ?SyncPromise
    {
        $baseUser = $context->baseUser();

        $path = $resolveInfo->path;
        array_pop($path);
        $path[] = 'personalDefaultFilter';

        return BatchLoaderRegistry::instance(
            $path,
            fn () => new RelationBatchLoader(
                new SimpleModelsLoader(
                    'personalSettings',
                    /** @phpstan-ignore-next-line Not sure how to typehint this one */
                    fn (HasMany $query) => $query->forMember($baseUser)
                )
            ),
        )->load($page)->then(function (Collection $personalSettings) {
            /** @var \App\Models\PersonalPageSettings $settings */
            $settings = $personalSettings->first() ?? new PersonalPageSettings;
            $id = $settings->settings->personalDefaultFilterId;

            return $id ? ModelBatchLoader::instanceFromModel(SavedFilter::class)->loadAndResolve($id) : null;
        });
    }

    protected function validateLists(array $lists, PageType $listType, Base $base): void
    {
        $globalId = resolve(GlobalId::class);
        foreach ($lists as $listId) {
            if (Str::contains($listId, '::')) {
                $accountId = $globalId->decodeID(strtok($listId, ':') ?: '');
                /** @var \AccountIntegrations\Models\IntegrationAccount $account */
                $account = $base->integrationAccounts->find($accountId);
                if (! \in_array(Scope::tryFrom($listType->value), $account->scopes, true)) {
                    $this->throwValidationException('lists', [trans('validation.lists')]);
                }
            } else {
                $type = $globalId->decodeType($listId);
                if (
                    ($listType === PageType::TODOS && $type !== 'TodoList')
                    || ($listType === PageType::CALENDAR && $type !== 'Calendar')
                    || ($listType === PageType::DOCUMENTS && $type !== 'Drive')
                    || ($listType === PageType::PINBOARD && $type !== 'Pinboard')
                    || ($listType === PageType::NOTES && $type !== 'Notebook')
                    || ($listType === PageType::LINKS && $type !== 'LinkList')
                ) {
                    $this->throwValidationException('lists', [trans('validation.lists')]);
                }
            }
        }
    }
}
