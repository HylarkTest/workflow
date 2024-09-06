<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Illuminate\Contracts\Support\Arrayable;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 *
 * @phpstan-type Shortcut array{ id: string, type: string }
 * @phpstan-type Widgets array{
 *    addShortcuts: \App\Core\Mappings\Features\MappingFeatureType[],
 * }
 */
class BaseUserPreferences implements Arrayable
{
    /**
     * @var Shortcut[]
     */
    public array $shortcuts;

    /**
     * @var Widgets
     */
    public array $widgets;

    /**
     * @var array{
     *     shortcuts: array{
     *         customize: 'FULL'|'SMALL'|'HIDE',
     *         integrations: 'FULL'|'SMALL'|'HIDE',
     *     },
     *     spaces: array<string, array{ pages: string[]|'ALL' }>
     * }
     */
    public array $homepage = [
        'shortcuts' => [
            'customize' => 'FULL',
            'integrations' => 'FULL',
        ],
        'spaces' => [],
    ];

    /**
     * @param array{
     *     shortcuts?: Shortcut[],
     *     widgets?: array{
     *         addShortcuts?: string[],
     *     },
     *     homepage?: array{
     *         shortcuts: array{
     *             customize: 'FULL'|'SMALL'|'HIDE',
     *             integrations: 'FULL'|'SMALL'|'HIDE',
     *         },
     *         spaces: array<string, array{ pages: string[]|'ALL' }>,
     *     }
     * } $preferences
     */
    public function __construct(array $preferences = [])
    {
        $this->shortcuts = $preferences['shortcuts'] ?? [];

        $this->setWidgets($preferences['widgets'] ?? []);

        if (isset($preferences['homepage'])) {
            $this->homepage = $preferences['homepage'];
        }
    }

    /**
     * @param array{
     *    addShortcuts?: string[],
     * } $widgets
     */
    public function setWidgets(array $widgets): void
    {
        $this->widgets = [
            'addShortcuts' => array_map(
                fn (string $type): MappingFeatureType => MappingFeatureType::from($type),
                $widgets['addShortcuts'] ?? ['NOTES']
            ),
        ];
    }

    public function toArray(): array
    {
        return [
            'shortcuts' => $this->shortcuts,
            'widgets' => [
                ...$this->widgets,
                'addShortcuts' => array_map(
                    static fn (MappingFeatureType $type): string => $type->value,
                    $this->widgets['addShortcuts'] ?? []
                ),
            ],
            'homepage' => [
                'shortcuts' => $this->homepage['shortcuts'] ?? [
                    'customize' => 'FULL',
                    'integrations' => 'FULL',
                ],
                'spaces' => $this->homepage['spaces'] ?? [],
            ],
        ];
    }
}
