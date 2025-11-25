<?php

declare(strict_types=1);


namespace Application\AppSettings;

use Application_FilterSettings;

/**
 * @property AppSettingsFilterCriteria $filters
 */
class AppSettingsFilterSettings extends Application_FilterSettings
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
