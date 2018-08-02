<?php

namespace jinchen\test\assets;

use jinchen\enum\Enum;

/**
 * @method static $this active()
 * @method static $this inactive()
 * @method static $this pending()
 */
class AmbiguousEnum extends Enum
{
    public const ACTIVE = ['value' => 1];
    public const INACTIVE = ['value' => 0];
    public const PENDING = ['value' => 1];
}