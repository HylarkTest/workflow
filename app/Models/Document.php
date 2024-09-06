<?php

declare(strict_types=1);

namespace App\Models;

use GraphQL\Deferred;
use Actions\Models\Action;
use App\Models\Concerns\CanBeFavorited;
use App\Models\Contracts\FeatureListItem;
use Mappings\Models\Document as BaseDocument;
use Actions\Models\Concerns\TranslatesActions;
use App\Models\Concerns\HasFeatureListItemMethods;
use Actions\Models\Contracts\ModelActionTranslator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon $favorited_at
 * @property \App\Models\Drive $drive
 *
 * @implements \App\Models\Contracts\FeatureListItem<\App\Models\Drive, \App\Models\Document>
 */
class Document extends BaseDocument implements FeatureListItem, ModelActionTranslator
{
    use CanBeFavorited;

    /** @use \App\Models\Concerns\HasFeatureListItemMethods<\App\Models\Drive> */
    use HasFeatureListItemMethods;

    use TranslatesActions;

    public const FILE_TYPE_MAP = [
        'IMAGE' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'heic', 'heif'],
        'PDF' => ['pdf'],
        'OFFICE' => ['doc', 'docx', 'xls', 'xlsx', 'ods', 'odt'],
    ];

    protected string $subjectDisplayNameKey = 'filename';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'space_id',
        'favorited_at',
    ];

    protected $casts = [
        'size' => 'int',
        'favorited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Drive, \App\Models\Document>
     */
    public function drive(): BelongsTo
    {
        return $this->belongsTo(Drive::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Drive, \App\Models\Document>
     */
    public function list(): BelongsTo
    {
        return $this->drive();
    }

    public static function directory(): string
    {
        return 'manager-documents';
    }

    public function shouldBeGloballySearchable(): bool
    {
        return (bool) $this->list;
    }

    public function getActionIgnoredColumns(): array
    {
        return ['filename', 'size', 'mime_type', 'url', 'extension'];
    }

    public static function formatDriveIdActionPayload(?int $driveId): ?Deferred
    {
        return static::formatListIdActionPayload($driveId);
    }

    public static function getActionChanges(Action $action): ?array
    {
        return null;
    }

    public function getSearchableName(): string
    {
        return $this->filename;
    }
}
