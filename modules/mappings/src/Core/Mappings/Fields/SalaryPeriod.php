<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

enum SalaryPeriod: string
{
    case HOURLY = 'HOURLY';
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';
    case ONE_TIME = 'ONE_TIME';
}
