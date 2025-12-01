<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application\Admin\Area\Mode\Submode\BaseAction;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class BaseRecordSettingsAction
    extends BaseAction
    implements RecordEditScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordEditScreenTrait;
}
