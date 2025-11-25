<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use DBHelper\Admin\Traits\RecordDeleteScreenInterface;
use DBHelper\Admin\Traits\RecordDeleteScreenTrait;

abstract class BaseRecordDeleteAction extends BaseRecordAction implements RecordDeleteScreenInterface
{
    use RecordDeleteScreenTrait;
}
