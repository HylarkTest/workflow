<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Color\Color;
use Finder\GloballySearchable;
use Actions\Models\Contracts\ActionSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelUtils\Database\Eloquent\Contracts\Sortable;

/**
 * @template TItem of \App\Models\Contracts\FeatureListItem
 * @template TList of \App\Models\Contracts\FeatureList
 *
 * @extends \LaravelUtils\Database\Eloquent\Contracts\Sortable<TList>
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * Attributes
 *
 * @property int $id
 * @property string $global_id
 * @property int $space_id
 * @property string $name
 * @property \Color\Color|null $color
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<TItem> $children
 * @property \App\Models\Space $space
 *
 * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
 */
interface FeatureList extends ActionSubject, GloballySearchable, Sortable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TItem>
     *
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    public function children(): HasMany;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Space, TList>
     *
     * @phpstan-ignore-next-line Spent too long on this, can't figure out how to make this work
     */
    public function space(): BelongsTo;

    public function colorOrDefault(): Color;

    /**
     * @return bool
     */
    public function restore();

    /**
     * @return bool
     */
    public function trashed();
}
