<?php

declare(strict_types=1);

namespace Actions\Models\Contracts;

interface ActionSubject extends ActionLimiter, ActionRecorderProvider, ActionSubjectNameProvider, ActionSubjectProvider, CustomActionProvider {}
