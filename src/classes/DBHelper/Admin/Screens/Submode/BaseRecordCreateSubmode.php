<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateSubmode extends Application_Admin_Area_Mode_Submode implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordCreateScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
