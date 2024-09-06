<?php

declare(strict_types=1);

namespace App\Core\Preferences;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, mixed>
 */
class PersonalPagePreferences implements Arrayable
{
    public ?int $personalDefaultFilterId = null;

    /**
     * @param array{
     *     personalDefaultFilterId?: int,
     * } $preferences
     */
    public function __construct(array $preferences = [])
    {
        $this->personalDefaultFilterId = $preferences['personalDefaultFilterId'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'personalDefaultFilterId' => $this->personalDefaultFilterId,
        ];
    }
}
