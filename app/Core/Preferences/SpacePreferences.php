<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Illuminate\Contracts\Support\Arrayable;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class SpacePreferences implements Arrayable
{
    /**
     * @var array<int, \App\Core\Mappings\Features\MappingFeatureType[]>
     */
    public array $markerGroups;

    /**
     * @param  array{markerGroups?: array<int, array<\App\Core\Mappings\Features\MappingFeatureType|string>|null>}  $preferences
     */
    public function __construct(array $preferences = [])
    {
        $this->markerGroups = [];
        foreach ($preferences['markerGroups'] ?? [] as $markerGroupId => $features) {
            $this->markerGroups[$markerGroupId] = array_map(
                fn (string|MappingFeatureType $feature) => \is_string($feature) ? MappingFeatureType::from($feature) : $feature,
                $features ?? []
            );
        }
    }

    public function toArray(): array
    {
        return [
            'markerGroups' => $this->markerGroups,
        ];
    }
}
