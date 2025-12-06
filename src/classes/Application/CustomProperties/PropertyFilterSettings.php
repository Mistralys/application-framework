<?php

declare(strict_types=1);

namespace Application\CustomProperties;

use DBHelper_BaseFilterSettings;

class PropertyFilterSettings extends DBHelper_BaseFilterSettings
{
    const string SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
        $this->configureSearch(self::SETTING_SEARCH);
    }
}
