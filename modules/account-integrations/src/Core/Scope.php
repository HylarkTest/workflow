<?php

declare(strict_types=1);

namespace AccountIntegrations\Core;

enum Scope: string
{
    case TODOS = 'TODOS';
    case CALENDAR = 'CALENDAR';
    case EMAILS = 'EMAILS';
    case DOCUMENTS = 'DOCUMENTS';
    case CONTACTS = 'CONTACTS';
}
