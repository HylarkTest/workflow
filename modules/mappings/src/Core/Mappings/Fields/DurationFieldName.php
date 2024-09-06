<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

enum DurationFieldName: string
{
    case MINUTES = 'MINUTES';
    case HOURS = 'HOURS';
    case DAYS = 'DAYS';
    case WEEKS = 'WEEKS';
    case MONTHS = 'MONTHS';
}
