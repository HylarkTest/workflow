<?php

declare(strict_types=1);

namespace App\Core;

use Actions\Models\Action;
use PHPStan\ShouldNotHappenException;

class MappingDesignAction extends Action
{
    public function changes(): ?array
    {
        return null;
    }

    public function description(bool $withPerformer = true): string
    {
        $translator = $this->getSubjectActionTranslator();

        if (! $translator) {
            return '';
        }

        $name = $translator->subjectNameFromAction($this);

        $translationKey = 'actions::description.mapping';

        if (
            $withPerformer
            && $performerName = $translator->performerNameFromAction($this)
        ) {
            $translationKey .= ".performer.$this->type";
            $description = __($translationKey, ['subject' => $name, 'performer' => $performerName]);
        } else {
            $translationKey .= ".$this->type";
            $description = __($translationKey, ['subject' => $name]);
        }
        if (\is_string($description)) {
            return $description;
        }
        throw new ShouldNotHappenException;
    }
}
