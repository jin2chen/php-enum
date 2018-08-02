<?php

namespace jinchen\test\assets;

use jinchen\enum\Enum;

/**
 * Class RequestEnum
 *
 * @method static $this fisPending()
 * @method static $this fisCompleted()
 */
class RequestEnum extends Enum
{
    public const FIS_PENDING = ['value' => 'pending'];
    public const FIS_COMPLETED = ['value' => 'completed'];
}