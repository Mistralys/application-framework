<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens;

use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_Action_CollectionRecord;
use DBHelper\Admin\Traits\RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait;

abstract class BaseRecordStatusAction extends Application_Admin_Area_Mode_Submode_Action_CollectionRecord implements RecordStatusScreenInterface
{
    use AllowableMigrationTrait;
    use RecordStatusScreenTrait;
}
