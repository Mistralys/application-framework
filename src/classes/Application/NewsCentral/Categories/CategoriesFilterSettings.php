<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use DBHelper_BaseFilterSettings;

class CategoriesFilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
    }

    protected function _configureFilters(): void
    {
        $this->filters->setSearch($this->getSetting(self::SETTING_SEARCH));
    }
}
