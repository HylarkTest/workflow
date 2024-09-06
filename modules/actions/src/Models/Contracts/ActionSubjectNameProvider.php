<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

interface ActionSubjectNameProvider
{
    public function getActionSubjectName(): ?string;

    public function subjectNameWasChanged(): bool;
}
