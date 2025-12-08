<?php

declare(strict_types=1);

namespace Application\Campaigns;

use AppUtils\ClassHelper;
use DBHelper;
use DBHelper_BaseCollection;

class CampaignCollection extends DBHelper_BaseCollection
{
    public const string TABLE_NAME = 'campaigns';
    public const string PRIMARY_NAME = 'campaign_id';
    public const string RECORD_TYPE = 'campaign';

    public const string COL_LABEL = 'campaign_label';
    public const string COL_ALIAS = 'campaign_alias';
    public const string COL_CREATED = 'campaign_created';
    public const string COL_USER_ID = 'user_id';

    public function getRecordClassName(): string
    {
        return CampaignRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return CampaignFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return CampaignFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    /**
     * @return array<string,string>
     */
    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label'),
            self::COL_ALIAS => t('Alias')
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
        return self::RECORD_TYPE;
    }

    public function getCollectionLabel(): string
    {
        return t('Campaigns');
    }

    public function getRecordLabel(): string
    {
        return t('Campaign');
    }

    public function getByID(int $record_id): CampaignRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            CampaignRecord::class,
            parent::getByID($record_id)
        );
    }

    public function aliasExists(string $alias) : bool
    {
        return $this->resolveIDByAlias($alias) !== null;
    }

    public function getByAlias(string $name) : CampaignRecord
    {
        $id = $this->resolveIDByAlias($name);
        if($id !== null) {
            return $this->getByID($id);
        }

        throw new CampaignException(
            ''
        );
    }

    public function resolveIDByAlias(string $alias) : ?int
    {
        $id = DBHelper::createFetchKey(self::PRIMARY_NAME, self::PRIMARY_NAME)
            ->whereValue(self::COL_ALIAS, $alias)
            ->fetchInt();

        if($id > 0) {
            return $id;
        }

        return null;
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_USER_ID)
            ->makeRequired()
            ->setCurrentUserGenerator()
            ->setUserValidation();

        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_ALIAS)
            ->makeRequired();

        $this->keys->register(self::COL_CREATED)
            ->makeRequired()
            ->setMicrotimeGenerator()
            ->setMicrotimeValidation();
    }

    public function createNewCampaign(string $alias, string $label): CampaignRecord
    {
        return $this->createNewRecord(array(
            self::COL_ALIAS => $alias,
            self::COL_LABEL => $label
        ));
    }

    public function createNewRecord(array $data = array(), bool $silent = false, array $options = array()): CampaignRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            CampaignRecord::class,
            parent::createNewRecord($data, $silent, $options)
        );
    }
}
