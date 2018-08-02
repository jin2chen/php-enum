<?php

namespace jinchen\test\assets;

use jinchen\enum\Enum;

/**
 * Class StatusEnum
 *
 * @method static $this active()
 * @method static $this inactive()
 * @method string label()
 */
class StatusEnum extends Enum
{
    public const ACTIVE = ['value' => 1, 'label' => 'active'];
    public const INACTIVE = ['value' => 0, 'label' => 'inactive'];
}