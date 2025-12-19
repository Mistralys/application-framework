<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\ClassLoaderScreenInterface;
use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordDeleteScreenInterface;
use DBHelper\Admin\Traits\RecordDeleteScreenTrait;

abstract class BaseRecordDeleteSubmode extends Application_Admin_Area_Mode_Submode implements RecordDeleteScreenInterface, ClassLoaderScreenInterface
{
    use RecordDeleteScreenTrait;

    public function getDefaultAction(): string
    {
        return '';
    }
}
