<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\DBHelper;

use DBHelper\Traits\LooseDBRecordInterface;
use LooseDBRecordTrait;

class LooseRecordStub implements LooseDBRecordInterface
{
    use LooseDBRecordTrait;

    protected function init(): void
    {
    }

    public function getRecordTable(): string
    {
        return '';
    }

    public function getRecordPrimaryName(): string
    {
        return '';
    }
}
