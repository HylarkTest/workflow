<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseType;
use App\Core\Groups\Role;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Markers\Core\MarkerType;
use App\Models\Concerns\HasImage;
use Laravel\Cashier\Subscription;
use App\Core\Account\AccountLimits;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Events\TenantSaved;
use Actions\Models\Concerns\HasActions;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Events\SavingTenant;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;
use Stancl\Tenancy\Events\TenantUpdated;
use Stancl\Tenancy\Events\CreatingTenant;
use Stancl\Tenancy\Events\DeletingTenant;
use Stancl\Tenancy\Events\UpdatingTenant;
use Actions\Models\Contracts\ActionSubject;
use Illuminate\Database\Eloquent\Collection;
use Stancl\Tenancy\Database\TenantCollection;
use Stancl\Tenancy\Database\Concerns\TenantRun;
use Illuminate\Database\Eloquent\Casts\Attribute;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Concerns\ManagesSubscriptions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property \App\Core\BaseType $type
 * @property string|null $imageUrl
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \App\Models\User $owner
 * @property \App\Models\BaseUserPivot $pivot
 * @property \App\Models\BaseSettings $settings
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Import> $imports
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\User> $members
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Space> $spaces
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $pages
 * @property \Illuminate\Database\Eloquent\Collection<\Mappings\Models\Category> $categories
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Mapping> $mappings
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Item> $items
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\MarkerGroup> $markerGroups
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\DeadlineGroup> $deadlineGroup
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\TodoList> $todoLists
 * @property \Illuminate\Database\Eloquent\Collection<\Planner\Models\Todo> $todos
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Action> $actions
 * @property \Illuminate\Database\Eloquent\Collection<\AccountIntegrations\Models\IntegrationAccount> $integrationAccounts
 * @property \Illuminate\Database\Eloquent\Collection<\Laravel\Cashier\Subscription> $subscriptions
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\AssigneeGroup> $assigneeGroups
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\SavedFilter> $savedFilters
 * @property \App\Models\AssigneeGroup $defaultAssigneeGroup
 */
class Base extends Model implements ActionSubject, Tenant
{
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasFactory;
    use HasImage;
    use HasInternalKeys;
    use InvalidatesResolverCache;
    use ManagesSubscriptions {
        subscriptions as cashierSubscriptions;
    }
    use TenantRun;

    public const MAX_BASES = 8;

    protected $guarded = [];

    protected array $actionIgnoredColumns = [
        'type',
    ];

    protected $dispatchesEvents = [
        'saving' => SavingTenant::class,
        'saved' => TenantSaved::class,
        'creating' => CreatingTenant::class,
        'created' => TenantCreated::class,
        'updating' => UpdatingTenant::class,
        'updated' => TenantUpdated::class,
        'deleting' => DeletingTenant::class,
        'deleted' => TenantDeleted::class,
    ];

    protected $casts = [
        'type' => BaseType::class,
    ];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): int
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    public function isPersonal(): bool
    {
        return $this->type->isPersonal();
    }

    public function isCollaborative(): bool
    {
        return $this->type->isCollaborative();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, never>
     */
    public function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->isPersonal()) {
                /** @phpstan-ignore-next-line  */
                return $this->owners->first()->avatarUrl;
            }
            $image = $this->image;
            if (! $image) {
                return null;
            }
            if (filter_var($image, \FILTER_VALIDATE_URL)) {
                return $image;
            }

            return $this->run(fn () => Storage::disk('images')->url($image));
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Import>
     */
    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User>
     */
    public function members(): BelongsToMany
    {
        return $this->unscopedBelongsToMany(User::class)
            ->using(BaseUserPivot::class)
            ->withPivot(['id', 'role', 'name', 'avatar', 'use_account_avatar', 'settings'])
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User>
     */
    public function owners(): BelongsToMany
    {
        return $this->unscopedBelongsToMany(User::class)
            ->using(BaseUserPivot::class)
            ->withPivot(['role'])
            ->wherePivot('role', Role::OWNER)
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\MemberInvite>
     */
    public function memberInvites(): HasMany
    {
        return $this->hasMany(MemberInvite::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\BaseSettings>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(BaseSettings::class)->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Space>
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Page>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Mappings\Models\Category>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(config('mappings.models.category'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapping>
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(config('mappings.models.mapping'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Item>
     */
    public function items(): HasMany
    {
        return $this->hasMany(config('mappings.models.item'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\MarkerGroup>
     */
    public function markerGroups(): HasMany
    {
        return $this->hasMany(config('markers.models.marker_group'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DeadlineGroup>
     */
    public function deadlineGroups(): HasMany
    {
        return $this->hasMany(DeadlineGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\TodoList>
     */
    public function todoLists(): HasMany
    {
        return $this->hasMany(config('planner.models.todo_list'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Todo>
     */
    public function todos(): HasMany
    {
        return $this->hasMany(config('planner.models.todo'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Calendar>
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(config('planner.models.calendar'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Event>
     */
    public function events(): HasMany
    {
        return $this->hasMany(config('planner.models.event'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Notebook>
     */
    public function notebooks(): HasMany
    {
        return $this->hasMany(config('notes.models.notebook'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Note>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(config('notes.models.note'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pinboard>
     */
    public function pinboards(): HasMany
    {
        return $this->hasMany(Pinboard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pin>
     */
    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Drive>
     */
    public function drives(): HasMany
    {
        return $this->hasMany(Drive::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Document>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Image>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LinkList>
     */
    public function linkLists(): HasMany
    {
        return $this->hasMany(LinkList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Link>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\SavedFilter>
     */
    public function savedFilters(): HasMany
    {
        return $this->hasMany(SavedFilter::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Action>
     */
    public function baseActions(): HasMany
    {
        return $this->hasMany(config('actions.model'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\AccountIntegrations\Models\IntegrationAccount>
     */
    public function integrationAccounts(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Marker>
     */
    public function markers(): HasMany
    {
        return $this->hasMany(config('markers.models.marker'));
    }

    public function createDefaultEntries(?Marker $adminTag = null): void
    {
        $this->run(function () use ($adminTag) {
            if (! $adminTag) {
                $adminTag = $this->createDefaultTags();
            }
            $this->createDefaultTodoLists($adminTag);
            $this->createDefaultCalendars();
            $pinboards = $this->createDefaultPinboards();
            $firstPinboard = $pinboards->first();
            foreach (['forest', 'mountain', 'snow'] as $pin) {
                $file = Image::createFromFile(new File(public_path("/images/defaultPins/$pin.jpeg")));
                $firstPinboard?->pins()->create([
                    'name' => Str::title($pin),
                    'document_id' => $file->id,
                ]);
            }
            $this->createDefaultDrives();
            $this->createDefaultNotebooks();
            $this->createDefaultLinkLists();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\AssigneeGroup>
     */
    public function assigneeGroups(): HasMany
    {
        return $this->hasMany(AssigneeGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\AssigneeGroup>
     */
    public function defaultAssigneeGroup(): HasOne
    {
        return $this->hasOne(AssigneeGroup::class)->where('is_default', true);
    }

    public function createDefaultAssigneeGroups(): void
    {
        $this->assigneeGroups()->create([
            'name' => 'Assignees',
            'is_default' => true,
        ]);
    }

    public function createDefaultTodoLists(?Marker $adminTag = null, bool $createdFirst = false): void
    {
        $this->spaces()
            ->doesntHave('defaultTodoLists')
            ->eachById(function (Space $space) use (&$createdFirst, $adminTag) {
                $list = $space->createDefaultTodoList();

                if (! $createdFirst) {
                    $createdFirst = true;
                    if ($this->type === BaseType::PERSONAL) {
                        $list->todos()->forceCreate([
                            'name' => 'Explore Hylark',
                            'priority' => 0,
                        ]);
                        /** @var \App\Models\Todo $activateTodo */
                        $activateTodo = $list->todos()->forceCreate([
                            'name' => 'Activate my Hylark account',
                            'priority' => 5,
                        ]);
                        if ($adminTag) {
                            $activateTodo->markers()->attach($adminTag);
                        }
                    } else {
                        $list->todos()->forceCreate([
                            'name' => 'Upload a logo or image to your base',
                            'priority' => 0,
                        ]);
                        $list->todos()->forceCreate([
                            'name' => 'Add custom tags, pipelines, and statuses',
                            'priority' => 0,
                        ]);
                    }
                }
            });
    }

    public function createDefaultCalendars(bool $createdFirst = false): void
    {
        $this->spaces()
            ->doesntHave('defaultCalendars')
            ->eachById(function (Space $space) use (&$createdFirst) {
                /** @var \App\Models\Calendar $calendar */
                $calendar = $space->createDefaultCalendar();

                if (! $createdFirst) {
                    $createdFirst = true;
                    if ($this->type === BaseType::PERSONAL) {
                        $calendar->events()->forceCreate([
                            'name' => 'Join Hylark',
                            'start_at' => today(),
                            'end_at' => today()->endOfDay(),
                            'timezone' => 'UTC',
                            'is_all_day' => true,
                        ]);
                    } else {
                        $calendar->events()->forceCreate([
                            'name' => 'Create a new Hylark base',
                            'start_at' => today(),
                            'end_at' => today()->endOfDay(),
                            'timezone' => 'UTC',
                            'is_all_day' => true,
                        ]);
                    }
                }
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pinboard>
     */
    public function createDefaultPinboards(): Collection
    {
        $pinboards = (new Pinboard)->newCollection();
        $this->spaces()->doesntHave('defaultPinboards')->eachById(function (Space $space) use ($pinboards) {
            $pinboards->push($space->createDefaultPinboard());
        });

        return $pinboards;
    }

    public function createDefaultNotebooks(): void
    {
        $this->spaces()->doesntHave('defaultNotebooks')->eachById(function (Space $space) {
            $space->createDefaultNotebook();
        });
    }

    public function createDefaultLinkLists(): void
    {
        $this->spaces()->doesntHave('defaultLinkLists')->eachById(function (Space $space) {
            $space->createDefaultLinkList();
        });
    }

    public function createDefaultDrives(): void
    {
        $this->spaces()->doesntHave('defaultDrives')->eachById(function (Space $space) {
            $space->createDefaultDrives();
        });
    }

    public function createDefaultTags(): Marker
    {
        /** @var \App\Models\MarkerGroup $group */
        $group = $this->markerGroups()
            ->create([
                'type' => MarkerType::TAG,
                'name' => 'General tags',
                'description' => 'Some common tags to get you started with categorizing data on your features.',
                'features' => array_map(
                    static fn (MappingFeatureType $type) => $type->value,
                    MappingFeatureType::markableFeatures()
                ),
            ]);

        $markers = $group->markers()->createMany(collect([
            ['name' => 'Important', 'color' => '#e29e88'],
            ['name' => 'Review', 'color' => '#cadb6a'],
            ['name' => 'Draft', 'color' => '#aeb3bc'],
            ['name' => 'Good', 'color' => '#9ad0ad'],
            ['name' => 'Needs more work', 'color' => '#e2ba88'],
            ['name' => 'To be improved', 'color' => '#e2d588'],
            ['name' => 'Great', 'color' => '#9588e2'],
            ['name' => 'Love this', 'color' => '#e288de'],
            ['name' => 'Attention', 'color' => '#b9676c'],
            ['name' => 'Admin', 'color' => '#d09acd'],
            ['name' => 'Phone', 'color' => '#6adb92'],
            ['name' => 'Email', 'color' => '#6a9ddb'],
            ['name' => 'Meeting', 'color' => '#a89e9e'],
        ])->map(fn (array $data, int $index) => ['order' => $index + 1, ...$data]));

        /** @phpstan-ignore-next-line I know there is a marker there */
        return $markers->last();
    }

    public function accountLimits(): AccountLimits
    {
        return new AccountLimits($this);
    }

    public function isActive(): bool
    {
        return $this->is(tenant());
    }

    public static function formatRoleActionPayload(?string $role): ?string
    {
        /** @var string|null $translation */
        $translation = $role ? trans("hylark.roles.$role") : null;

        return $translation;
    }

    public function premiumPlanName(): string
    {
        return $this->isPersonal() ? 'ascend' : 'soar';
    }

    public function getActiveSubscription(): ?Subscription
    {
        $subscription = $this->subscription($this->premiumPlanName());
        if (! $subscription || ! $subscription->valid()) {
            return null;
        }

        return $subscription;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed($this->premiumPlanName());
    }

    public function getCurrentPlan(): string
    {
        return $this->getActiveSubscription()?->name ?? 'core';
    }

    public function plan(): array
    {
        $plan = $this->getCurrentPlan();
        $accountLimits = $this->accountLimits();

        $features = [
            'storage',
            'pages',
            'spaces',
            'records',
            'tagGroups',
            'pipelineGroups',
            'statusGroups',
            'categories',
            'integrations',
            'pins' => 'pinboard',
            'notes',
            'links',
            'todos',
            'events',
        ];

        $featureMapFunction = function (bool $limitInsteadOfUsed) use ($accountLimits) {
            return function (string $feature, $key) use ($limitInsteadOfUsed, $accountLimits) {
                if (\is_int($key)) {
                    $key = $feature;
                }
                $pricingKey = Str::snake($feature);
                if ($limitInsteadOfUsed) {
                    $result = $accountLimits->getLimit($pricingKey);
                } else {
                    $result = $accountLimits->getExistingAmount($pricingKey);
                }

                return [$key => $result];
            };
        };

        return [
            'name' => $plan,
            'features' => collect($features)->mapWithKeys($featureMapFunction(true)),
            'used' => collect($features)->mapWithKeys($featureMapFunction(false)),
            'historyLimit' => $accountLimits->getLimit('log'),
        ];
    }

    public static function booted(): void
    {
        self::created(static function (self $base) {
            $base->run(fn () => $base->createDefaultAssigneeGroups());
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Laravel\Cashier\Subscription>
     */
    public function subscriptions()
    {
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany<\Laravel\Cashier\Subscription> $query */
        $query = $this->cashierSubscriptions()->withoutGlobalScope('base');

        return $query;
    }

    public function initialize(): void
    {
        tenancy()->initialize($this);
    }
}
