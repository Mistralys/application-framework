<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens;

use Application_Admin_Area_Mode_Submode;
use Application_Traits_Admin_CollectionSettings;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class BaseSubmodeRecordSettingsScreen
    extends Application_Admin_Area_Mode_Submode
    implements
    RecordEditScreenInterface
{
    use Application_Traits_Admin_CollectionSettings;
    use RecordEditScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
