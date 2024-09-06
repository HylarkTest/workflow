<?php

declare(strict_types=1);

namespace Markers\Core;

enum MarkerType: string
{
    case TAG = 'TAG';
    case STATUS = 'STATUS';
    case PIPELINE = 'PIPELINE';
}
