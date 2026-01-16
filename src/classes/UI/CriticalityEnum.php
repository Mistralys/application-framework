<?php

declare(strict_types=1);

namespace UI;

use BasicEnum;

class CriticalityEnum extends BasicEnum
{
    public const string DANGEROUS = 'important';
    public const string INFO = 'info';
    public const string SUCCESS = 'success';
    public const string WARNING = 'warning';
    public const string INVERSE = 'inverse';
    public const string INACTIVE = 'default';
}
