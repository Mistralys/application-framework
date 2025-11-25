<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListAction
    extends Application_Admin_Area_Mode_Submode_Action
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;
}
