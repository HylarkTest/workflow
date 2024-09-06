<?php

declare(strict_types=1);

namespace App\CustomActions;

use App\Models\Action;
use Illuminate\Support\Arr;
use App\Core\ActionTranslator;

class MappingCreateAction extends Action
{
    public function changes(): ?array
    {
        $translator = $this->getSubjectActionTranslator();

        $payload = $this->getPayload();

        if (! $payload || ! $translator) {
            return null;
        }

        return ActionTranslator::mapPayload($payload, function ($change, $original, $event, $field) use ($translator) {
            return [
                'description' => $translator->translateEvent($this, $event, $field),
                'before' => $original,
                'after' => $change,
                'type' => $field === 'description' ? 'paragraph' : 'line',
            ];
        });
    }

    protected function getPayload(): array
    {
        return Arr::only($this->payload ?: [], ['name', 'singular_name', 'description']);
    }
}
