<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class BaseRecordListMode
    extends BaseMode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }
}
