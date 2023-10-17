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
    public const SETTING_SEARCH = 'search';
    public const SETTING_TYPE = 'type';

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_TYPE, t('Type'));
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
    }

    protected function inject_type() : void
    {
        $el = $this->addElementSelect(self::SETTING_TYPE);
        $el->addOption(t('Any'), '');

        $types = NewsEntryTypes::getInstance()->getAll();
        foreach($types as $type)
        {
            $el->addOption($type->getLabel(), $type->getID());
        }
    }

    protected function _configureFilters(): void
    {
        $this->filters->setSearch($this->getSetting(self::SETTING_SEARCH));

        $this->configureType();
    }

    /**
     * @return void
     * @throws CollectionException
     */
    private function configureType(): void
    {
        $collection = NewsEntryTypes::getInstance();
        $type = $this->getSetting(self::SETTING_TYPE);

        if (!empty($type) && $collection->idExists($type)) {
            $this->filters->selectType($collection->getByID($type));
        }
    }
}
