<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class BaseRecordSettingsAction
    extends Application_Admin_Area_Mode_Submode_Action
    implements
    RecordEditScreenInterface
{
    use AllowableMigrationTrait;
    use RecordSettingsScreenTrait;
    use RecordEditScreenTrait;
}
