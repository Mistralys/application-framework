<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application_RevisionableCollection;
use Application_Traits_Disposable;
use Application_Traits_Eventable;
use Application_User;
use TestDriver\Revisionables\Storage\RevisionableStorage;
use TestDriver\Revisionables\Storage\RevisionCopy;

/**
 * @method RevisionableRecord createNewRecord(string $label, ?Application_User $author = null, array $data = array())
 */
class RevisionableCollection extends Application_RevisionableCollection
{
    public const TABLE_NAME = 'revisionables';
    public const TABLE_REVISIONS = 'revisionables_revisions';
    public const TABLE_CURRENT_REVISIONS = 'revisionables_current_revisions';
    public const TABLE_CHANGELOG = 'revisionables_changelog';

    public const PRIMARY_NAME = 'revisionable_id';
    public const COL_REV_ID = 'revisionable_revision';
    public const COL_REV_STRUCTURAL = 'structural';
    public const COL_REV_ALIAS = 'alias';

    use Application_Traits_Eventable;
    use Application_Traits_Disposable;

    private static ?RevisionableCollection $instance = null;

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new RevisionableCollection();
        }

        return self::$instance;
    }

    public function createNewRevisionable(string $label, string $alias) : RevisionableRecord
    {
        return $this->createNewRecord(
            $label,
            null,
            array(
                self::COL_REV_ALIAS => $alias
            )
        );
    }

    // region: X - Interface methods

    public function getIdentification(): string
    {
        return 'Revisionables';
    }

    public function getChildDisposables(): array
    {
        return array();
    }

    protected function _dispose(): void
    {
    }

    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }

    public function getRecordClassName() : string
    {
        return RevisionableRecord::class;
    }

    public function getRecordTypeName() : string
    {
        return 'Revisionable';
    }

    public function getRecordFiltersClassName() : string
    {
        return RevisionableFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return RevisionableFilterSettings::class;
    }

    public function getRevisionsStorageClass() : string
    {
        return RevisionableStorage::class;
    }

    public function getAdminURLParams() : array
    {
        return array();
    }

    public function getRecordReadableNameSingular() : string
    {
        return t('Revisionable');
    }

    public function getRecordReadableNamePlural() : string
    {
        return t('Revisionables');
    }

    public function getRecordCopyRevisionClass() : string
    {
        return RevisionCopy::class;
    }

    protected function initCustomArguments(array $arguments = array()): void
    {
    }

    public function getRecordSearchableColumns() : array
    {
        return array(
            self::COL_REV_LABEL => t('Label'),
            self::COL_REV_ALIAS => t('Alias')
        );
    }

    public function getCurrentRevisionsTableName(): string
    {
        return self::TABLE_CURRENT_REVISIONS;
    }

    public function getPrimaryKeyName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRevisionsTableName(): string
    {
        return self::TABLE_REVISIONS;
    }

    public function getRevisionKeyName(): string
    {
        return self::COL_REV_ID;
    }

    public function getRecordChangelogTableName(): string
    {
        return self::TABLE_CHANGELOG;
    }

    // endregion
}
