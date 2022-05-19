<?php

declare(strict_types=1);

namespace UI;

use BasicEnum;

class CriticalityEnum extends BasicEnum
{
    public const DANGEROUS = 'important';
    public const INFO = 'info';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const INVERSE = 'inverse';
    public const INACTIVE = 'default';
}
