<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use TestApplication\TestDriver\Revisionables\RevisionableMemory;
use Application\Revisionable\RevisionableInterface;
use Application_RevisionableCollection;
use Application_Traits_Disposable;
use Application_Traits_Eventable;
use TestDriver\Revisionables\Storage\RevisionableStorage;
use TestDriver\Revisionables\Storage\RevisionCopy;

class RevisionableMemoryCollection extends Application_RevisionableCollection
{
    use Application_Traits_Eventable;
    use Application_Traits_Disposable;

    protected function _getIdentification(): string
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
        return RevisionableMemory::class;
    }

    public function getRecordTypeName(): string
    {
        return 'RevisionableMemory';
    }

    public function getRecordFiltersClassName(): string
    {
        return RevisionableFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return RevisionableFilterSettings::class;
    }

    public function getRevisionsStorageClass(): string
    {
        return RevisionableStorage::class;
    }

    public function getAdminListURL(array $params = array()): string
    {
        return '';
    }

    public function getAdminURLParams(): array
    {
        return array();
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
        return RevisionCopy::class;
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

    public function getPrimaryRequestName(): string
    {
        return '';
    }
}
