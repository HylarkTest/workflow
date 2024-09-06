<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mappings\Core\Mappings\Contracts\MappingContainer;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Interface Owner
 *
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Space> $spaces
 *
 * @mixin Model
 */
interface Owner extends MappingContainer
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Space>|\Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Space>|\Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Space>|\Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Space>
     */
    public function spaces(): MorphMany|MorphToMany|BelongsToMany|HasMany;
}
