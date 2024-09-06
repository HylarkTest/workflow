<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Base;
use App\Models\Item;
use App\Models\Page;
use App\Models\User;
use App\Models\Space;
use App\Core\BaseType;
use App\Models\Mapping;
use Tests\DuskTestCase;
use App\Core\Groups\Role;
use App\Models\MarkerGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mappings\Models\Category;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\artisan;

use AccountIntegrations\Core\Scope;
use Illuminate\Testing\TestResponse;
use App\Models\Contracts\FeatureList;
use AccountIntegrations\Core\Provider;
use Tests\Concerns\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use App\Models\Contracts\FeatureListItem;
use App\Core\Preferences\SpacePreferences;
use Illuminate\Database\Eloquent\Collection;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;

uses(DuskTestCase::class)->in('Browser');
uses(DatabaseMigrations::class)->in('Browser');

uses(TestCase::class)->in('Feature', 'Unit');

function createIntegrationAccount(
    User $user,
    Provider $provider = Provider::MICROSOFT,
    array $scopes = [Scope::CALENDAR, Scope::TODOS, Scope::EMAILS]
): IntegrationAccount {
    /** @var \AccountIntegrations\Models\IntegrationAccount $integration */
    $integration = $user->integrationAccounts()->create([
        'account_name' => 'test@mail.com',
        'provider' => $provider,
        'scopes' => $scopes,
        'provider_id' => '123',
        'token' => '123',
        'refresh_token' => '123',
        'expires_at' => now()->addDay(),
    ]);

    return $integration;
}

function tenantFn($cb): Closure
{
    return fn () => auth()->user()->firstPersonalBase()->run($cb);
}

function createUser($attributes = [], $initialize = true): User
{
    $user = create(User::class, $attributes);
    if ($initialize) {
        tenancy()->initialize($user->firstPersonalBase());
    }

    return $user;
}

function buildQueryFromExpectedResponse(array $response): string
{
    return '{'.array_reduce(
        array_keys($response),
        static function ($query, $key) use ($response) {
            $gqlKey = $key;
            $value = $response[$gqlKey];

            if (str_starts_with($key, '...on ')) {
                $query .= ' __typename';
            }
            $query .= ' '.$gqlKey;
            if ($value instanceof NullFieldWithSubQuery) {
                $query .= ' '.$value->subQuery;
            } elseif (is_array($value)) {
                if (is_int(key($value))) {
                    $value = $value[0];
                }
                if (is_array($value)) {
                    $query .= ' '.buildQueryFromExpectedResponse($value);
                }
            }

            return $query;
        },
        ''
    ).' }';
}

// Just to appease the linter
class Pest {}
class Ignore {}

class JSONField
{
    public function __construct(public array $value) {}
}

class NullFieldWithSubQuery
{
    public function __construct(public string $subQuery, public bool $isList = false) {}
}
function clearArgumentsAndFragments(array $expectedResponse, array $actualResponse): array
{
    $clearedResponse = [];
    foreach ($expectedResponse as $key => $value) {
        if (is_string($key)) {
            if (preg_match('/^\w+:/', $key)) {
                $key = strtok($key, ':');
            } elseif (str_contains($key, '(')) {
                $key = strtok($key, '(');
            }
        }
        if (is_string($key) && str_starts_with($key, '...on ')) {
            $type = substr($key, 6);
            if ($actualResponse['__typename'] === $type) {
                foreach (clearArgumentsAndFragments($value, $actualResponse) as $subKey => $subValue) {
                    $clearedResponse[$subKey] = $subValue;
                }
            }
        } elseif (is_array($value)) {
            $clearedResponse[$key] = clearArgumentsAndFragments($value, $actualResponse[$key] ?? []);
        } elseif ($value instanceof NullFieldWithSubQuery) {
            $clearedResponse[$key] = $value->isList ? [] : null;
        } elseif ($value instanceof JSONField) {
            $clearedResponse[$key] = $value->value;
        } elseif (! ($value instanceof Ignore)) {
            $clearedResponse[$key] = $value;
        }
    }

    return $clearedResponse;
}

function buildSimpleQuery(array|string $path): string
{
    $path = is_array($path) ? $path : explode('.', $path);

    return array_reduce(
        array_reverse($path),
        fn ($query, $key) => "$key { $query }",
    );
}

function enableAllActions(): void
{
    config([
        'actions.automatic' => true,
        'actions.mandatory_performer' => false,
    ]);
}

function createMapping(User|Base $owner, array $attributes = []): Mapping
{
    $base = $owner instanceof Base ? $owner : $owner->firstPersonalBase();

    return $base->run(fn () => $base->mappings()->save(make(Mapping::class, array_merge([
        'space_id' => $base->spaces()->first()->id,
    ], $attributes))));
}

function createEntitiesPage(User|Base|Mapping $owner, array $attributes = []): Page
{
    $mapping = $owner instanceof Mapping ? $owner : createMapping($owner);
    $base = $mapping->base;

    return $base->run(fn () => $base->pages()->save(make(Page::class, array_merge([
        'name' => 'My page',
        'space_id' => $mapping->space_id,
        'type' => 'ENTITIES',
        'mapping_id' => $mapping->id,
    ], $attributes))));
}

function createEntityPage(User|Base|Mapping $owner, array $attributes = []): Page
{
    return createEntitiesPage($owner, array_merge($attributes, ['type' => 'ENTITY']));
}

function createListsPage(User|Base $owner, array $attributes = []): Page
{
    $base = $owner instanceof Base ? $owner : $owner->firstPersonalBase();

    return $base->run(fn () => $base->pages()->save(make(Page::class, array_merge([
        'name' => 'My page',
        'space_id' => $base->spaces->first()->id,
        'type' => 'TODOS',
    ], $attributes))));
}

/**
 * @return \App\Models\Item|Collection<\App\Models\Item>
 */
function createItem(Mapping $mapping, array $data = ['SYSTEM_NAME' => ['_v' => 'Larry']], int $count = 1): Item|Collection
{
    $items = $mapping->items()->saveMany(
        Item::factory()->count($count)->make(['data' => $data]),
    );

    return $count === 1 ? $items->first() : $items;
}

function createSpace(Base $base, array $attributes = []): Space
{
    return $base->spaces()->save(make(Space::class, $attributes));
}

function createList(User|Base $user, string $query, array $attributes = [], int $withChildren = 0): FeatureList
{
    if ($user instanceof Base) {
        $oldTenant = tenant();
        tenancy()->initialize($user);
    }
    $space = $user instanceof Base ? $user->spaces->first() : $user->firstSpace();
    /** @var \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Contracts\FeatureList> $relation */
    $relation = $space->{Str::plural($query)}();
    $factory = $relation->getModel()::factory();
    if ($withChildren) {
        $factory = $factory->withChildren($withChildren);
    }

    $return = $factory->create([
        'space_id' => $space,
        ...$attributes,
    ]);

    if ($user instanceof Base) {
        tenancy()->initialize($oldTenant);
    }

    return $return;
}

function createListItem(FeatureList $list, array $attributes = []): FeatureListItem
{
    return $list->children()->save(make(
        get_class($list->children()->getModel()),
        [
            $list->children()->getForeignKeyName() => $list->id,
            ...$attributes,
        ],
    ));
}

function createMarkerGroup(User|Base $user, array $attributes = [], int $withMarkers = 0): MarkerGroup
{
    $base = $user instanceof Base ? $user : $user->firstPersonalBase();

    $factory = MarkerGroup::factory();
    if ($withMarkers) {
        $factory = $factory->withMarkers($withMarkers);
    }

    $markerGroup = $factory->create([
        'base_id' => $base,
        ...$attributes,
    ]);

    $base->spaces->each(fn (Space $space) => $space->updatePreferences(function (SpacePreferences $preferences) use ($markerGroup) {
        $preferences->markerGroups[$markerGroup->id] = MappingFeatureType::markableFeatures();
    }));

    return $markerGroup;
}

function convertToFileRequest($query, $variables): TestResponse
{
    $body = compact('query', 'variables');
    $dotVariables = Arr::dot(compact('variables'));
    $files = array_filter($dotVariables, static fn ($field) => $field instanceof UploadedFile);

    if (! $files) {
        return test()->postJson('graphql', $body);
    }

    foreach ($files as $key => $ignore) {
        Arr::set($body, $key, null);
    }

    return test()->call(
        'POST',
        'graphql',
        [
            'operations' => json_encode($body, \JSON_THROW_ON_ERROR),
            'map' => json_encode((object) array_map(static fn ($key) => [$key], array_keys($files)), \JSON_THROW_ON_ERROR),
        ],
        [],
        array_values($files),
        test()->transformHeadersToServerVars([
            'Content-Type' => 'multipart/form-data',
        ])
    );
}

function createCategory(): Category
{
    /** @var \Mappings\Models\Category $category */
    $category = Category::query()->forceCreate(['name' => 'Careers']);
    $category->items()->createMany(collect(['Chef', 'Teacher', 'Developer'])->map(fn ($career) => ['name' => $career]));

    return $category;
}

function switchToBase(Base $base, ?User $user = null): void
{
    if (! $user) {
        $user = auth()->user() ?: $base->owners->first();
    }
    $user->setActiveBase($base);
    tenancy()->initialize($base);
}

function createCollabUser(Role $role = Role::OWNER, ?Base $base = null): User
{
    $user = createUser();
    if ($base) {
        $base->members()->attach($user, ['role' => $role]);
    } else {
        $base = $user->bases()->create([
            'name' => 'Collab Base',
            'type' => BaseType::COLLABORATIVE,
        ], ['role' => $role]);
        $base->run(fn () => createSpace($base));
    }
    $user->unsetRelation('bases');

    test()->be($user);
    switchToBase($base);

    return $user;
}

function fakeStorage($disk): void
{
    config(['tenancy.bootstrappers' => array_filter(config('tenancy.bootstrappers'), fn ($bootstrapper) => $bootstrapper !== FilesystemTenancyBootstrapper::class)]);
    Storage::fake($disk);
}

function useDatabaseQueue(): void
{
    config(['queue.default' => 'database']);
    artisan(MigrateCommand::class, [
        '--path' => 'vendor/orchestra/testbench-core/laravel/migrations/queue/0001_01_01_000000_testbench_create_jobs_table.php',
    ])->execute();
}

function testCSV()
{
    return <<<'CSV'
    Full name,Profession,email1,email2,birthday
    Anakin Skywalker,Jedi,as@jedi.com,dv@sith.com,03/09/41
    Leia Organa,Princess,lo@senate.com,,01/01/19
    Obi-wan Kenobi,Jedi,ok@jedi.com,ben@tatooine.com,13/09/57
    Padmé Amidala,Senator,pa@senate.com,,23/09/46
    Luke Skywalker,Jedi,ls@rebels.com,ls@jedi.com,19/09/19
    CSV;
}

// Recursive function to find all images in the array
function findImages($array, &$images = [])
{
    foreach ($array as $key => $value) {
        if ($key === 'image') {
            $images[] = $value;
        }
        if (is_array($value)) {
            findImages($value, $images);
        }
    }

    return $images;
}
