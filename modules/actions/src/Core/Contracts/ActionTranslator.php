<?php

declare(strict_types=1);

namespace Actions\Core\Contracts;

use Actions\Models\Action;
use Illuminate\Database\Eloquent\Model;

interface ActionTranslator
{
    public function subjectName(Model $subject): string;

    public function subjectNameFromAction(Action $action): string;

    public function performerName(Model $performer): ?string;

    public function performerNameFromAction(Action $action): ?string;

    public function setSubjectName(Action $action, Model $subject): Action;

    public function setPerformerName(Action $action, Model $performer): Action;

    public function actionDescription(Action $action, bool $withPerformer = true): string;

    public function actionChanges(Action $action): ?array;

    public function buildChangesFromPayload(Action $action): array;

    public function translateEvent(Action $action, string $event, string $field, mixed $original = null, mixed $change = null): string;

    public function translateDescription(Action $action, bool $withPerformer): string;
}
