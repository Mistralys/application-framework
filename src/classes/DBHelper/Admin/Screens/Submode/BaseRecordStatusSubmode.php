<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use DBHelper\Admin\Traits\RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait;

abstract class BaseRecordStatusSubmode extends BaseRecordSubmode implements RecordStatusScreenInterface
{
    use RecordStatusScreenTrait;

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }
}
