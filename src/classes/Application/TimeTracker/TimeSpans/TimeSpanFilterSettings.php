<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use DBHelper_BaseFilterSettings;

class TimeSpanFilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
        $this->configureSearch(self::SETTING_SEARCH);
    }
}
