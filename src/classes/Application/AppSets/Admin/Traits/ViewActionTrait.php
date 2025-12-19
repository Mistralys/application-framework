<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Traits;

use Application\AppSets\AppSet;
use Application\AppSets\AppSetsCollection;
use Application\Sets\Admin\Screens\Submode\ViewSubmode;
use AppUtils\ClassHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

trait ViewActionTrait
{
    public function getParentScreenClass(): string
    {
        return ViewSubmode::class;
    }

    private function resolveAppSet(DBHelperRecordInterface $record): AppSet
    {
        return ClassHelper::requireObjectInstanceOf(AppSet::class, $record);
    }

    public function getRecordMissingURL(): string|AdminURLInterface
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function createCollection(): AppSetsCollection
    {
        return AppSetsCollection::getInstance();
    }

    public function getRecord(): AppSet
    {
        return ClassHelper::requireObjectInstanceOf(
            AppSet::class,
            $this->record
        );
    }

    public function getCollection(): AppSetsCollection
    {
        return $this->createCollection();
    }
}
