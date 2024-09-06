<?php

declare(strict_types=1);

namespace Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use LighthouseHelpers\Concerns\HasGlobalId;

/**
 * Class Document
 *
 * @property int $id
 * @property string $filename
 * @property string $url
 * @property int $size
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Document extends Model
{
    use HasGlobalId;

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, ['filename']);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Documents\Models\Document>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Documents\Models\Document>
     */
    public function scopeIn(Builder $query, string $in): Builder
    {
        $in = trim($in, '/').'/';
        $slashCount = mb_substr_count($in, '/');
        $groupExpression = "SUBSTRING_INDEX(filename, '/', $slashCount)";

        return $query->select([
            'id' => new Expression('case when count(*) > 1 then null else MIN('.$this->getKeyName().') end'),
            'filename' => $groupExpression,
            'url' => new Expression('case when count(*) > 1 then null else MIN(url) end'),
            'size' => new Expression('SUM(size)'),
            'created_at' => new Expression('MIN(created_at)'),
            'updated_at' => new Expression('MAX(updated_at)'),
        ])->groupByRaw($groupExpression);
    }
}
