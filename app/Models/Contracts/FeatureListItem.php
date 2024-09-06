<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Finder\GloballySearchable;
use Markers\Models\MarkableModel;
use Actions\Models\Contracts\ActionSubject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TList of \App\Models\Contracts\FeatureList
 * @template TItem of \App\Models\Contracts\FeatureListItem
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property TList $list
 *
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Item> items()
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\BaseUserPivot> assignees()
 */
interface FeatureListItem extends ActionSubject, Assignable, GloballySearchable, MarkableModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<TList, TItem>
     *
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    public function list(): BelongsTo;

    /**
     * @return bool
     */
    public function trashed();

    /**
     * @return bool
     */
    public function restore();

    public function typeName(): string;
}
