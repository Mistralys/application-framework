<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class BaseRecordSettingsSubmode
    extends Application_Admin_Area_Mode_Submode
    implements
    RecordEditScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordEditScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
