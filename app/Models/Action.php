<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Actions\Core\ActionType;
use Illuminate\Database\Eloquent\Model;
use Actions\Models\Action as BaseAction;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Actions\PrivateActionSubject;
use LighthouseHelpers\Core\ModelBatchLoader;
use App\Models\Concerns\HasBaseScopedRelationships;

/**
 * @property bool $is_private
 */
class Action extends BaseAction
{
    use HasBaseScopedRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payload',
        'context_info',
        'private',
    ];

    protected $casts = [
        'payload' => 'json',
        'is_latest' => 'boolean',
        'type' => ActionType::class,
        'is_private' => 'bool',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('limitPrivate', function ($query) {
            $tenant = tenant();
            if ($tenant) {
                $performer = $tenant->pivot;
                $query->where(function (Builder $query) use ($performer) {
                    $query->where('is_private', false);
                    if ($performer instanceof BaseUserPivot) {
                        $query->orWhere(function (Builder $query) use ($performer) {
                            $query->where('performer_id', $performer->getKey())
                                ->where('performer_type', $performer->getMorphClass());
                        });
                    }
                });
            }
        });
    }

    public function description(?bool $withPerformer = true): string
    {
        return parent::description($withPerformer === null ? true : $withPerformer);
    }

    public function deferredSubject(): ?Deferred
    {
        if ($this->getAttribute('subject_id') === null) {
            return null;
        }
        $relationship = $this->subject();

        return ModelBatchLoader::instanceFromModel(
            \get_class($relationship->getRelated())
        )->load($relationship->getParentKey());
    }

    public function save(array $options = [])
    {
        if ($this->relationLoaded('subject') && $this->subject instanceof Base) {
            return $this->subject->run(fn () => parent::save($options));
        }

        return parent::save($options);
    }

    /*
     * Used for bulk action inserts.
     */
    protected function getAttributesForInsert()
    {
        $attributes = parent::getAttributesForInsert();
        $base = tenancy()->tenant;
        if ($base) {
            $attributes['base_id'] = $base->getKey();
        }

        return $attributes;
    }

    public function setSubject(Model $subject): BaseAction
    {
        parent::setSubject($subject);

        if ($subject instanceof PrivateActionSubject && $subject->isPrivateAction($this)) {
            $this->is_private = true;
        }

        return $this;
    }
}
