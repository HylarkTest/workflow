<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields;

enum AddressFieldName: string
{
    case LINE1 = 'LINE1';
    case LINE2 = 'LINE2';
    case CITY = 'CITY';
    case STATE = 'STATE';
    case COUNTRY = 'COUNTRY';
    case POSTCODE = 'POSTCODE';
}
