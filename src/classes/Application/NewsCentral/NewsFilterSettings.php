<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use AppUtils\Collections\CollectionException;
use DBHelper_BaseFilterSettings;

/**
 * @property NewsFilterCriteria $filters
 */
class NewsFilterSettings extends DBHelper_BaseFilterSettings
{
    public const string SETTING_SEARCH = 'search';
    public const string SETTING_TYPE = 'type';
    public const string SETTING_STATUS = 'status';

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_TYPE, t('Type'));
        $this->registerSetting(self::SETTING_STATUS, t('Status'));
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
    }

    protected function inject_type() : void
    {
        $el = $this->addElementSelect(self::SETTING_TYPE);
        $el->addOption(t('Any'), '');

        $items = NewsEntryTypes::getInstance()->getAll();
        foreach($items as $item)
        {
            $el->addOption($item->getLabel(), $item->getID());
        }
    }

    protected function inject_status() : void
    {
        $el = $this->addElementSelect(self::SETTING_STATUS);
        $el->addOption(t('Any'), '');

        $items = NewsEntryStatuses::getInstance()->getAll();
        foreach($items as $item)
        {
            $el->addOption($item->getLabel(), $item->getID());
        }
    }

    protected function _configureFilters(): void
    {
        $this->filters->setSearch($this->getSetting(self::SETTING_SEARCH));

        $this->configureType();
        $this->configureStatus();
    }

    /**
     * @return void
     * @throws CollectionException
     */
    private function configureType(): void
    {
        $collection = NewsEntryTypes::getInstance();
        $id = $this->getSetting(self::SETTING_TYPE);

        if (!empty($id) && $collection->idExists($id)) {
            $this->filters->selectType($collection->getByID($id));
        }
    }

    private function configureStatus() : void
    {
        $collection = NewsEntryStatuses::getInstance();
        $id = $this->getSetting(self::SETTING_STATUS);

        if (!empty($id) && $collection->idExists($id)) {
            $this->filters->selectStatus($collection->getByID($id));
        }
    }
}
