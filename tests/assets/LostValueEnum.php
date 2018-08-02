<?php

namespace jinchen\test\assets;

use jinchen\enum\Enum;

class LostValueEnum extends Enum
{
    public const ACTIVE = ['value' => 1, 'label' => 'active'];
    public const InACTIVE = ['label' => 'inactive'];
}