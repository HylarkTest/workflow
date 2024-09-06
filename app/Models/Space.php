<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Arr;
use App\Models\Contracts\Domain;
use Actions\Models\Concerns\HasActions;
use Illuminate\Support\Facades\Storage;
use App\Core\Preferences\SpacePreferences;
use App\Models\Concerns\HasSettingsColumn;
use Actions\Models\Contracts\ActionSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mappings\Core\Mappings\Contracts\MappingContainer;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

/**
 * Class Space
 *
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string $color
 * @property \App\Core\Preferences\SpacePreferences $settings
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Page> $pages
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\TodoList> $todoList
 */
class Space extends Model implements ActionSubject, Domain, MappingContainer
{
    use AdvancedSoftDeletes;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasFactory;

    /**
     * @use \App\Models\Concerns\HasSettingsColumn<\App\Core\Preferences\SpacePreferences>
     */
    use HasSettingsColumn;

    public array $deleteCascadeRelationships = [
        'calendars' => 'queue',
        'todoLists' => 'queue',
        'notebooks' => 'queue',
        'pinboards' => 'queue',
        'linkLists' => 'queue',
        'mappings',
        'drives' => 'queue',
        'pages',
    ];

    /**
     * @var class-string<\App\Core\Preferences\SpacePreferences>
     */
    protected string $settingsClass = SpacePreferences::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapping>
     *
     * @phpstan-ignore-next-line Not sure how to get it matching parent type
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(Mapping::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Page>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model>
     */
    public function owner(): MorphTo
    {
        /** @phpstan-ignore-next-line Not sure how to get it matching parent type */
        return $this->morphTo('owner');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\TodoList>
     */
    public function todoLists(): HasMany
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\TodoList>
     */
    public function defaultTodoLists(): HasMany
    {
        return $this->todoLists()->where('is_default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Calendar>
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Calendar>
     */
    public function defaultCalendars(): HasMany
    {
        return $this->calendars()->where('is_default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Notebook>
     */
    public function notebooks(): HasMany
    {
        return $this->hasMany(Notebook::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pinboard>
     */
    public function pinboards(): HasMany
    {
        return $this->hasMany(Pinboard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LinkList>
     */
    public function linkLists(): HasMany
    {
        return $this->hasMany(LinkList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Drive>
     */
    public function drives(): HasMany
    {
        return $this->hasMany(Drive::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\LinkList>
     */
    public function defaultLinkLists(): HasMany
    {
        return $this->linkLists()->where('is_default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Drive>
     */
    public function defaultDrives(): HasMany
    {
        return $this->drives()->where('is_default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pinboard>
     */
    public function defaultPinboards(): HasMany
    {
        return $this->pinboards()->where('is_default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Notebook>
     */
    public function defaultNotebooks(): HasMany
    {
        return $this->notebooks()->where('is_default', true);
    }

    public function createDefaultCalendar(): Calendar
    {
        return $this->calendars()->create([
            'name' => 'General',
            'is_default' => true,
        ]);
    }

    public function createDefaultTodoList(): TodoList
    {
        return $this->todoLists()->create([
            'name' => 'Inbox',
            'is_default' => true,
        ]);
    }

    public function createDefaultNotebook(): Notebook
    {
        return $this->notebooks()->create([
            'name' => 'General',
            'is_default' => true,
        ]);
    }

    public function createDefaultPinboard(): Pinboard
    {
        return $this->pinboards()->create([
            'name' => 'General',
            'is_default' => true,
        ]);
    }

    public function createDefaultLinkList(): LinkList
    {
        return $this->linkLists()->create([
            'name' => 'General',
            'is_default' => true,
        ]);
    }

    public function createDefaultDrives(): Drive
    {
        return $this->drives()->create([
            'name' => 'General',
            'is_default' => true,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, string>
     */
    public function logo(): Attribute
    {
        return Attribute::get(function (?string $logo): ?string {
            if (! $logo) {
                return null;
            }
            if (filter_var($logo, \FILTER_VALIDATE_URL)) {
                return $logo;
            }

            return Storage::url($logo);
        });
    }

    /**
     * @return \App\Core\Mappings\Features\MappingFeatureType[]
     */
    public function enabledMarkerFeatures(MarkerGroup $group): array
    {
        return $this->settings->markerGroups[$group->id] ?? [];
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType[]  $features
     */
    public function enableMarkerFeatures(MarkerGroup $group, array $features): void
    {
        $this->updatePreferences(function (SpacePreferences $preferences) use ($group, $features): void {
            $preferences->markerGroups[$group->id] = $features;
        });
    }

    /**
     * @param  \App\Core\Mappings\Features\MappingFeatureType[]  $features
     * @return int[]
     */
    public function markerGroupsWithEnabledFeatures(array $features): array
    {
        $features = Arr::pluck($features, 'value');

        return collect($this->settings->markerGroups)->filter(function (array $enabledFeatures) use ($features): bool {
            return \count(array_intersect($features, Arr::pluck($enabledFeatures, 'value'))) === \count($features);
        })->keys()->toArray();
    }
}
