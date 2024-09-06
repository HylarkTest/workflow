<?php

declare(strict_types=1);

namespace App\DataIntegrity;

class DataIntegrity
{
    public array $events = [];

    public function getEvents(): array
    {
        return $this->events;
    }
}
