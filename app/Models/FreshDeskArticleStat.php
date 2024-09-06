<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\NotScoped;

/**
 * Attributes
 *
 * @property int $article_id
 * @property int $views
 * @property int $thumbs_up
 * @property int $thumbs_down
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class FreshDeskArticleStat extends Model implements NotScoped
{
    public $incrementing = false;

    protected $table = 'fresh_desk_article_stats';

    protected $primaryKey = 'article_id';

    protected $fillable = [
        'article_id',
        'views',
        'thumbs_up',
        'thumbs_down',
    ];

    public function getConnectionName()
    {
        // Override the default connection
        return config('database.default');
    }
}
