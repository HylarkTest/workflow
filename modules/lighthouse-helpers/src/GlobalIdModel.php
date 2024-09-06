<?php

declare(strict_types=1);

namespace LighthouseHelpers;

use Nuwave\Lighthouse\GlobalId\GlobalId;

/**
 * Interface GlobalIdModel
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface GlobalIdModel
{
    public static function getGlobalIdService(): GlobalId;
}
