<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Support\Facades\DB;
use Mappings\Events\MappingCreating;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Mappings\Core\Mappings\MappingType;
use Mappings\Core\Mappings\Fields\Field;
use Mappings\Events\AttributeItemRemoving;
use Mappings\Core\Mappings\Fields\FieldType;
use Mappings\Core\Mappings\Sections\Section;
use Mappings\Database\Factories\MappingFactory;
use Mappings\Core\Mappings\Fields\FieldCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mappings\Core\Mappings\Relationships\Relationship;
use Mappings\Core\Mappings\Sections\SectionCollection;
use Mappings\Core\Mappings\Relationships\RelationshipCollection;
use LaravelUtils\Database\Eloquent\Concerns\HasCollectionAttributes;

/**
 * Class Mapping
 *
 * @property int $id
 * @property string $name
 * @property string $api_name
 * @property string $singular_name
 * @property string $api_singular_name
 * @property string|null $description
 * @property \Mappings\Core\Mappings\MappingType $type
 * @property \Mappings\Core\Mappings\Fields\FieldCollection $fields
 * @property \Mappings\Core\Mappings\Relationships\RelationshipCollection $relationships
 * @property \Mappings\Core\Mappings\Sections\SectionCollection $sections
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Model $owner
 * @property \Illuminate\Database\Eloquent\Collection<\Mappings\Models\Item> $items
 *
 * @phpstan-import-type RelationshipOptions from \Mappings\Core\Mappings\Relationships\Relationship
 * @phpstan-import-type SectionOptions from \Mappings\Core\Mappings\Sections\Section
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class Mapping extends Model
{
    use HasCollectionAttributes {
        getCollectionAttributes as traitGetCollectionAttributes;
    }
    use HasFactory;

    public const MAX_TITLE_LENGTH = 50;

    public const MAX_DESCRIPTION_LENGTH = 2000;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'creating' => MappingCreating::class,
    ];

    /**
     * The attributes that should be cast to enums
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => MappingType::class,
    ];

    /**
     * @var array<string, class-string>
     */
    protected array $collectionAttributes = [];

    /**
     * @return array<string, string>
     */
    public function getCollectionAttributes(): array
    {
        return array_merge([
            'fields' => FieldCollection::class,
            'relationships' => RelationshipCollection::class,
            'sections' => SectionCollection::class,
        ], $this->traitGetCollectionAttributes());
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array<int, string>
     */
    public function getFillable()
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'name',
            'api_name',
            'singular_name',
            'api_singular_name',
            'description',
            'type',
            'fields',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, \Mappings\Models\Mapping>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Mappings\Models\Item>
     */
    public function items(): HasMany
    {
        $relation = $this->hasMany(config('mappings.models.item'));
        /** @var \Mappings\Models\Item $model */
        $model = $relation->getModel();
        $model->setRelation('mapping', $this);

        return $relation;
    }

    /**
     * @param  FieldOptions  $options
     */
    public function addField(array $options): ?Field
    {
        /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
        $field = $this->saveToCollection('fields', $options);

        return $field;
    }

    public function removeField(string $id): ?Field
    {
        /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
        $field = $this->removeFromCollection('fields', $id);

        return $field;
    }

    /**
     * @param  FieldOptions  $options
     */
    public function updateField(string $id, array $options): ?Field
    {
        /** @var \Mappings\Core\Mappings\Fields\Field|null $field */
        $field = $this->changeInCollection('fields', $id, $options);

        return $field;
    }

    /**
     * @param  SectionOptions  $options
     */
    public function addSection(array $options): ?Section
    {
        /** @var \Mappings\Core\Mappings\Sections\Section|null $section */
        $section = $this->saveToCollection('sections', $options);

        return $section;
    }

    public function removeSection(string $id): ?Section
    {
        /** @var \Mappings\Core\Mappings\Sections\Section|null $section */
        $section = $this->removeFromCollection('sections', $id);

        return $section;
    }

    /**
     * @param  SectionOptions  $options
     */
    public function updateSection(string $id, array $options): ?Section
    {
        /** @var \Mappings\Core\Mappings\Sections\Section|null $section */
        $section = $this->changeInCollection('sections', $id, $options);

        return $section;
    }

    /**
     * @param  RelationshipOptions  $options
     */
    public function addRelationship(array $options): ?Relationship
    {
        /** @var \Mappings\Core\Mappings\Relationships\Relationship|null $relationship */
        $relationship = $this->saveToCollection('relationships', $options);

        return $relationship;
    }

    public function removeRelationship(string $id): ?Relationship
    {
        /** @var \Mappings\Core\Mappings\Relationships\Relationship|null $relationship */
        $relationship = $this->removeFromCollection('relationships', $id);

        return $relationship;
    }

    /**
     * @param  array{
     *     name?: string,
     *     apiName?: string,
     * }  $options
     */
    public function updateRelationships(string $id, array $options): ?Relationship
    {
        /** @var \Mappings\Core\Mappings\Relationships\Relationship|null $relationship */
        $relationship = $this->changeInCollection('relationships', $id, $options);

        return $relationship;
    }

    /**
     * @param  \Mappings\Core\Mappings\Fields\FieldType|array<int, \Mappings\Core\Mappings\Fields\FieldType>  $type
     */
    public function fieldsOfType(FieldType|array $type): FieldCollection
    {
        return $this->fields->filter(function (Field $field) use ($type) {
            if (\is_array($type)) {
                return collect($type)->some(fn (FieldType $type) => $field->type()->is($type));
            }

            return $field->type()->is($type);
        })->values();
    }

    /**
     * @param  string[]|null  $sections
     */
    public function queryFields(?array $sections = null): ?FieldCollection
    {
        $fields = $this->fields;
        if ($sections) {
            return $fields->whereIn('section', $sections, true);
        }

        return $fields;
    }

    /**
     * @return \Mappings\Database\Factories\MappingFactory
     */
    protected static function newFactory()
    {
        return MappingFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::$dispatcher->listen(
            AttributeItemRemoving::class,
            static function (AttributeItemRemoving $event) {
                $mapping = $event->model;
                if ($event->attribute === 'sections') {
                    /** @var \Mappings\Models\Mapping $mapping */
                    $id = $event->item->id();

                    $fields = $mapping->fields;
                    $fields->each(static function (Field $field) use ($id) {
                        if ($field->getSection() === $id) {
                            $field->setSection(null);
                        }
                    });
                    $mapping->setAttribute('fields', $fields);
                }

                if ($event->attribute === 'relationships') {
                    /** @var \Mappings\Core\Mappings\Relationships\Relationship $relationship */
                    $relationship = $event->item;
                    try {
                        $toMapping = $relationship->toMapping();
                    } catch (\Exception $e) {
                        $toMapping = null;
                    }
                    if (! $toMapping || ! $toMapping->relationships->find($relationship->id())) {
                        DB::table('relationships')
                            ->whereExists(static function (Builder $query) use ($event, $relationship) {
                                $query->from('items')->selectRaw('1')
                                    ->whereColumn('relationships.base_id', 'items.base_id')
                                    ->whereColumn('relationships.related_id', 'items.id')
                                    ->where('items.mapping_id', $event->model->getKey())
                                    ->where('relationships.relation_id', $relationship->id());
                            })->delete();
                    }
                }
            }
        );
    }
}
