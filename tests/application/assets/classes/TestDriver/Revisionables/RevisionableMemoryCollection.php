<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use TestApplication\TestDriver\Revisionables\RevisionableMemory;
use Application\Revisionable\RevisionableInterface;
use Application_RevisionableCollection;
use Application_Traits_Disposable;
use Application_Traits_Eventable;

class RevisionableMemoryCollection extends Application_RevisionableCollection
{
    use Application_Traits_Eventable;
    use Application_Traits_Disposable;

    public function getIdentification(): string
    {
        return 'RevisionableMemoryCollection';
    }

    public function getChildDisposables(): array
    {
        return array();
    }

    protected function _dispose(): void
    {
    }

    protected function initCustomArguments(array $arguments = array()): void
    {
    }

    public function getRecordTableName(): string
    {
        return '';
    }

    public function getRecordClassName(): string
    {
        return \TestApplication\TestDriver\Revisionables\RevisionableMemory::class;
    }

    public function getRecordTypeName(): string
    {
        return 'RevisionableMemory';
    }

    public function getRecordFiltersClassName(): string
    {
        return '';
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return '';
    }

    public function getRevisionsStorageClass(): string
    {
        return '';
    }

    public function getAdminListURL(array $params = array()): string
    {
        return '';
    }

    public function getAdminURLParams(): array
    {
        return '';
    }

    public function getRecordReadableNameSingular(): string
    {
        return 'Memory Revisionable';
    }

    public function getRecordReadableNamePlural(): string
    {
        return 'Memory Revisionables';
    }

    public function getRecordCopyRevisionClass(): string
    {
        return '';
    }

    public function getRecordSearchableColumns(): array
    {
        return array();
    }

    public function getCurrentRevisionsTableName(): string
    {
        return '';
    }

    public function getPrimaryKeyName(): string
    {
        return '';
    }

    public function getTableName(): string
    {
        return '';
    }

    public function getRevisionsTableName(): string
    {
        return '';
    }

    public function getRevisionKeyName(): string
    {
        return '';
    }

    public function getRecordChangelogTableName(): string
    {
        return '';
    }

    public function canRecordBeDestroyed(RevisionableInterface $revisionable): bool
    {
        return true;
    }
}
