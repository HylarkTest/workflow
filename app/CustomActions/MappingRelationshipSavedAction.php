<?php

declare(strict_types=1);

namespace App\CustomActions;

use App\Models\Mapping;
use Actions\Models\Action;
use Illuminate\Support\Arr;
use App\Core\ActionTranslator;
use LighthouseHelpers\Core\ModelBatchLoader;
use Mappings\Core\Mappings\Relationships\RelationshipType;

class MappingRelationshipSavedAction extends Action
{
    public function changes(): ?array
    {
        $translator = $this->getSubjectActionTranslator();

        $payload = $this->getPayload();

        if (! $payload || ! $translator) {
            return null;
        }

        return ActionTranslator::mapPayload($payload, function ($change, $original, $event, $field) use ($translator) {
            if ($field === 'to') {
                $change = ModelBatchLoader::instanceFromModel(
                    config('mappings.models.mapping')
                )->loadAndResolve(
                    $change, [],
                    fn (?Mapping $mapping): string => $mapping->name ?? '['.trans('common.deleted').']'
                );
            }
            if ($field === 'type') {
                $change = RelationshipType::coerce($change)->getDescription();
            }

            return [
                'description' => $translator->translateEvent($this, $event, $field),
                'before' => $original,
                'after' => $change,
                'type' => 'line',
            ];
        });
    }

    public function getPayload(): array
    {
        return Arr::except($this->payload ?? [], ['apiName']);
    }
}
