<?php

declare(strict_types=1);

namespace Mappings\Core\Timestamps;

enum DateTimeStringFormat: string
{
    public function dateTimeFormat(): string
    {
        if ($this->name === 'DATETIME') {
            return 'Y-m-d H:i:s';
        }

        return \constant(\DateTime::class.'::'.$this->name);
    }
    case DATETIME = 'DATETIME';
    case ATOM = 'ATOM';
    case COOKIE = 'COOKIE';
    case ISO8601 = 'ISO8601';
    case RSS = 'RSS';
    case W3C = 'W3C';
}
