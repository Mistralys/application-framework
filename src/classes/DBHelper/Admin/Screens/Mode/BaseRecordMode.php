<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

abstract class BaseRecordMode
    extends BaseMode
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
