<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings;

enum MappingType: string
{
    case PERSON = 'PERSON';
    case ITEM = 'ITEM';
}
