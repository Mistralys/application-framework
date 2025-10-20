<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application_Admin_Area_Mode;
use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListMode
    extends Application_Admin_Area_Mode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;

    final public function getDefaultSubmode(): string
    {
        return '';
    }
}
