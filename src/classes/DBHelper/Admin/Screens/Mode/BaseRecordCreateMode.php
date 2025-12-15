<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordCreateScreenTrait;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateMode extends BaseMode implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;
    use RecordCreateScreenTrait;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass() : ?string
    {
        return null;
    }
}
