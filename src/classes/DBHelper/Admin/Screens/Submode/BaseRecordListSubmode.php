<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListSubmode
    extends BaseSubmode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;

    public function getDefaultAction(): string
    {
        return '';
    }
}
