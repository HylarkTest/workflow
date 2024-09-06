<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Support\Facades\Config;

/**
 * Class Document
 *
 * @property int $id
 * @property string $filename
 * @property int $size
 * @property string $url
 * @property string $extension
 */
class Image extends Document
{
    protected $table = 'images';

    public function url(): string
    {
        return static::fileSystem()->url($this->url);
    }

    protected static function directory(): string
    {
        return 'item-images';
    }

    protected static function disk(): string
    {
        return Config::get('mappings.filesystems.images');
    }
}
