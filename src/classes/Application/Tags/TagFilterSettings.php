<?php

declare(strict_types=1);

namespace Application\Tags;

use DBHelper_BaseFilterSettings;

class TagFilterSettings extends DBHelper_BaseFilterSettings
{
    const SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
    }

    protected function _configureFilters(): void
    {
        $this->configureSearch(self::SETTING_SEARCH);
    }
}
