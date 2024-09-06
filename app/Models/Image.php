<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Models\Action;
use Actions\Models\Concerns\HasActions;
use Mappings\Models\Image as BaseImage;
use Actions\Models\Contracts\ActionSubject;
use LighthouseHelpers\Concerns\HasGlobalId;
use Actions\Models\Concerns\TranslatesActions;
use Actions\Models\Contracts\ModelActionTranslator;
use App\Models\Concerns\HasBaseScopedRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LighthouseHelpers\Concerns\ConvertsCamelCaseAttributes;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

class Image extends BaseImage implements ActionSubject, ModelActionTranslator
{
    use AdvancedSoftDeletes;
    use ConvertsCamelCaseAttributes;
    use HasActions;
    use HasBaseScopedRelationships;
    use HasFactory;
    use HasGlobalId;
    use TranslatesActions;

    protected string $subjectDisplayNameKey = 'filename';

    public static function directory(): string
    {
        return 'manager-images';
    }

    public function getActionIgnoredColumns(): array
    {
        return ['filename', 'size', 'mime_type', 'url', 'extension'];
    }

    public static function getActionChanges(Action $action): ?array
    {
        return null;
    }
}
