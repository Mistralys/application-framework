<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application\Admin\Area\Mode\Submode\BaseAction;
use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListAction
    extends BaseAction
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;
}
