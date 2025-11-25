<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application_Admin_Area_Mode;
use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

abstract class BaseRecordMode
    extends Application_Admin_Area_Mode
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
