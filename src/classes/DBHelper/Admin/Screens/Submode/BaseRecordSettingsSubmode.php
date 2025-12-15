<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class BaseRecordSettingsSubmode
    extends BaseSubmode
    implements RecordEditScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordEditScreenTrait;

    public function getDefaultAction(): string
    {
        return '';
    }
}
