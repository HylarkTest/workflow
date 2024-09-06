<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use Actions\Core\ActionTranslator;

class MappingUpdateAction extends Action
{
    public function changes(): ?array
    {
        $translator = $this->getSubjectActionTranslator();

        if (! $this->payload || ! $translator) {
            return null;
        }

        return ActionTranslator::mapPayload($this->payload, function ($change, $original, $event, $field) use ($translator) {
            $translation = $translator->translateEvent($this, $event, $field);

            if ($field === 'icon') {
                return ['description' => $translation];
            }

            return [
                'description' => $translation,
                'before' => $original,
                'after' => $change,
                'type' => $field === 'description' ? 'paragraph' : 'line',
            ];
        });
    }
}
