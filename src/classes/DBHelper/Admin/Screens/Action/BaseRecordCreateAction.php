<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateAction extends Application_Admin_Area_Mode_Submode_Action implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordCreateScreenTrait;

    public function getDefaultSubscreenClass() : null
    {
        return null;
    }
}
