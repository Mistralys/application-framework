<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateSubmode
    extends BaseSubmode
    implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordCreateScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
