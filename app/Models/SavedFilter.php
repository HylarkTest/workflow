<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Core\ActionType;
use PHPStan\ShouldNotHappenException;
use Actions\Models\Concerns\HasActions;
use Actions\Models\Contracts\ActionSubject;
use Actions\Models\Contracts\ModelActionRecorder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Core\Actions\ActionTypes\SavedFilterActionType;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;

/**
 * Attributes
 *
 * @property int $id
 * @property string $name
 * @property array $filters
 * @property array $order_by
 * @property ?string $group
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Accessors
 * @property-read bool $private
 *
 * Relationships
 * @property-read \App\Models\Model $filterable
 */
class SavedFilter extends Model implements ActionSubject, ModelActionRecorder
{
    use ConvertsCamelCaseAttributes;
    use HasActions;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
        'order_by' => 'array',
    ];

    protected $fillable = [
        'name',
        'filters',
        'order_by',
        'group',
        'base_user_id',
    ];

    protected array $actionIgnoredColumns = [
        'filterable_id',
        'filterable_type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<BaseModel, \App\Models\SavedFilter>
     */
    public function filterable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<bool, bool>
     */
    public function private(): Attribute
    {
        return Attribute::get(function () {
            return $this->getAttribute('base_user_id') !== null;
        });
    }

    public function getActionSubject(\Actions\Models\Action $action): Model
    {
        return $this->filterable;
    }

    public function getActionType(?BaseModel $performer, ?ActionType $baseType): ActionType
    {
        return match (true) {
            $baseType?->is(ActionType::CREATE()) && ! $this->private => SavedFilterActionType::SAVED_FILTER_CREATE(),
            $baseType?->is(ActionType::UPDATE()) && ! $this->private => SavedFilterActionType::SAVED_FILTER_UPDATE(),
            $baseType?->is(ActionType::DELETE()) && ! $this->private => SavedFilterActionType::SAVED_FILTER_DELETE(),
            $baseType?->is(ActionType::CREATE()) && $this->private => SavedFilterActionType::PRIVATE_SAVED_FILTER_CREATE(),
            $baseType?->is(ActionType::UPDATE()) && $this->private => SavedFilterActionType::PRIVATE_SAVED_FILTER_UPDATE(),
            $baseType?->is(ActionType::DELETE()) && $this->private => SavedFilterActionType::PRIVATE_SAVED_FILTER_DELETE(),
            default => throw new ShouldNotHappenException,
        };
    }

    public function getActionPayload(ActionType $type, ?BaseModel $performer): ?array
    {
        $type = match (true) {
            $type->is(SavedFilterActionType::SAVED_FILTER_CREATE()),
            $type->is(SavedFilterActionType::PRIVATE_SAVED_FILTER_CREATE()) => ActionType::CREATE(),
            $type->is(SavedFilterActionType::SAVED_FILTER_UPDATE()),
            $type->is(SavedFilterActionType::PRIVATE_SAVED_FILTER_UPDATE()) => ActionType::UPDATE(),
            $type->is(SavedFilterActionType::SAVED_FILTER_DELETE()),
            $type->is(SavedFilterActionType::PRIVATE_SAVED_FILTER_DELETE()) => ActionType::DELETE(),
            default => throw new ShouldNotHappenException,
        };

        /** @var \Actions\Core\ActionRecorder $recorder */
        $recorder = static::getActionRecorder();

        return $recorder->getPayload($this, $type, $performer);
    }
}
