<?php

declare(strict_types=1);

use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListSubmode
    extends Application_Admin_Area_Mode_Submode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;

    final public function getDefaultAction() : string
    {
        return '';
    }
}
