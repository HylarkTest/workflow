<?php

declare(strict_types=1);

namespace App\CustomActions;

use Actions\Models\Action;
use App\Core\ActionTranslator;

class MappingUpdateAction extends Action
{
    public function changes(): ?array
    {
        $translator = $this->getSubjectActionTranslator();

        if (! $this->payload || ! $translator) {
            return null;
        }

        return ActionTranslator::mapPayload($this->payload, function ($change, $original, $event, $field) use ($translator) {
            return [
                'description' => $translator->translateEvent($this, $event, $field),
                'before' => $original,
                'after' => $change,
                'type' => $field === 'description' ? 'paragraph' : 'line',
            ];
        });
    }
}
