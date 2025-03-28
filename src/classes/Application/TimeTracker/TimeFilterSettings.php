<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use DBHelper_BaseFilterSettings;

class TimeFilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
    }
}