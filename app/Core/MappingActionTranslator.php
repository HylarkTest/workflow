<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;

class MappingActionTranslator extends ActionTranslator
{
    protected GlobalId $globalId;

    public function __construct(Repository $config, Translator $translator, GlobalId $globalId)
    {
        parent::__construct($config, $translator);
        $this->globalId = $globalId;
    }

    public function actionChanges(Action $action): ?array
    {
        if ($action->type->in([
            MappingActionType::REMOVE_MAPPING_FEATURE(),
            MappingActionType::ADD_MAPPING_FEATURE(),
            MappingActionType::CHANGE_MAPPING_FEATURE(),
            MappingActionType::REMOVE_MAPPING_RELATIONSHIP(),
            MappingActionType::REMOVE_MAPPING_FIELD(),
            MappingActionType::REMOVE_MAPPING_TAG_GROUP(),
            MappingActionType::ADD_MAPPING_TAG_GROUP(),
        ])) {
            return null;
        }

        return $this->buildChangesFromPayload($action);
    }
}
