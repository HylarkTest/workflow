<?php

declare(strict_types=1);

namespace App\Models;

use Actions\Models\Concerns\HasActions;
use Database\Factories\MarkerGroupFactory;
use Actions\Models\Contracts\ActionSubject;
use LaravelUtils\Database\Eloquent\Casts\CSV;
use App\Core\Mappings\Features\MappingFeatureType;
use Markers\Models\MarkerGroup as BaseMarkerGroup;
use App\Models\Concerns\HasBaseScopedRelationships;

/**
 * Class MarkerGroup
 *
 * @property array $features
 * @property string[]|null $template_refs
 */
class MarkerGroup extends BaseMarkerGroup implements ActionSubject
{
    use HasActions;
    use HasBaseScopedRelationships;

    protected $fillable = [
        'template_refs',
    ];

    protected $casts = [
        'template_refs' => CSV::class,
    ];

    public function getActionIgnoredColumns(): array
    {
        return ['type'];
    }

    public static function formatFeaturesActionPayload(?string $features): string
    {
        $features = $features ? explode(',', $features) : [];

        return implode(', ', array_map(
            fn ($feature) => MappingFeatureType::from($feature)->toLocaleString(),
            $features
        ));
    }

    protected static function newFactory(): MarkerGroupFactory
    {
        return MarkerGroupFactory::new();
    }
}
