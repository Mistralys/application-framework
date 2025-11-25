<?php

declare(strict_types=1);

use DBHelper\Admin\Traits\RecordDeleteScreenInterface;
use DBHelper\Admin\Traits\RecordDeleteScreenTrait;

abstract class BaseRecordDeleteSubmode extends Application_Admin_Area_Mode_Submode implements RecordDeleteScreenInterface
{
    use RecordDeleteScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
