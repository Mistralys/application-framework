<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Traits;

use Application\AppSets\AppSet;
use Application\Sets\Admin\Screens\AppSetsDevelMode;
use AppUtils\ClassHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;

trait SubmodeTrait
{
    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getParentScreenClass(): string
    {
        return AppSetsDevelMode::class;
    }

    private function resolveAppSet(DBHelperRecordInterface $record): AppSet
    {
        return ClassHelper::requireObjectInstanceOf(AppSet::class, $record);
    }
}
