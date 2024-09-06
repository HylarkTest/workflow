<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class BasePreferences implements Arrayable
{
    public string $accentColor = '#381bf3';

    /**
     * @var array{
     *     spaces: array<string, array{ pages: string[] }>
     * }
     */
    public array $homepage = [
        'spaces' => [],
    ];

    /**
     * @param  array{
     *     accentColor?: string,
     *     homepage?: array{
     *        spaces: array<string, array{ pages: string[] }>
     *     }
     * }  $preferences
     */
    public function __construct(array $preferences = [])
    {
        if (isset($preferences['accentColor'])) {
            $this->accentColor = $preferences['accentColor'];
        }

        if (isset($preferences['homepage'])) {
            $this->homepage = $preferences['homepage'];
        }
    }

    public function toArray(): array
    {
        return [
            'accentColor' => $this->accentColor,
            'homepage' => [
                'spaces' => $this->homepage['spaces'] ?? [],
            ],
        ];
    }
}
