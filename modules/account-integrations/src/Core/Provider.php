<?php

declare(strict_types=1);

namespace AccountIntegrations\Core;

enum Provider: string
{
    case MICROSOFT = 'MICROSOFT';
    // case APPLE = 'APPLE';
    case GOOGLE = 'GOOGLE';
}
