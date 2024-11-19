<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\Taggables\TagCollectionTrait;
use Application\Tags\Taggables\TagConnector;
use Application\Tags\Taggables\TaggableInterface;
use DBHelper;
use DBHelper_BaseCollection;
use TestDriver\OfflineEvents\RegisterTagCollections\RegisterTestDBCollection;

/**
 * @method TestDBRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 * @method TestDBRecord getByID(int $record_id)
 * @method TestDBFilterCriteria getFilterCriteria()
 * @method TestDBFilterSettings getFilterSettings()
 * @method TestDBTagConnector getTagConnector()
 */
class TestDBCollection extends DBHelper_BaseCollection implements TagCollectionInterface
{
    public const COLLECTION_ID = 'test_db_records';

    use TagCollectionTrait;

    public const TABLE_NAME = 'test_records';
    public const TABLE_NAME_DATA = 'test_records_data';

    public const PRIMARY_NAME = 'record_id';

    public const COL_ALIAS = 'alias';
    public const COL_LABEL = 'label';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if(self::$instance === null) {
            self::$instance = DBHelper::createCollection(self::class, null, true);
        }

        return self::$instance;
    }

    public function getCollectionRegistrationClass(): string
    {
        return RegisterTestDBCollection::class;
    }

    public function getCollectionID(): string
    {
        return self::COLLECTION_ID;
    }

    public function getRecordClassName(): string
    {
        return TestDBRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return TestDBFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TestDBFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return 'test_record';
    }

    public function getCollectionLabel(): string
    {
        return t('Test records');
    }

    public function getRecordLabel(): string
    {
        return t('Test record');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    public function createTestRecord(string $label, string $alias): TestDBRecord
    {
        return $this->createNewRecord(array(
            self::COL_LABEL => $label,
            self::COL_ALIAS => $alias
        ));
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_ALIAS)
            ->makeRequired();
    }

    // region: Tagging

    public const TABLE_NAME_TAGS = 'test_records_tags';
    public const TAG_REGISTRY_KEY = 'test_record_tags';

    public function getTaggableTypeLabel() : string
    {
        return t('Test record');
    }

    public function getTaggableByID(int $id): TestDBRecord
    {
        return $this->getByID($id);
    }

    public function getTagConnectorClass(): ?string
    {
        return TestDBTagConnector::class;
    }

    public function getTagPrimary(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getTagTable(): string
    {
        return self::TABLE_NAME_TAGS;
    }

    public function getTagSourceTable(): string
    {
        return self::TABLE_NAME;
    }

    public function getTagRegistryKey(): string
    {
        return self::TAG_REGISTRY_KEY;
    }

    // endregion: Tagging
}
