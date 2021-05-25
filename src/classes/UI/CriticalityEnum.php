<?php

declare(strict_types=1);

class UI_CriticalityEnum extends BasicEnum
{
    const DANGEROUS = 'important';
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const INVERSE = 'inverse';
    const INACTIVE = 'default';
}
