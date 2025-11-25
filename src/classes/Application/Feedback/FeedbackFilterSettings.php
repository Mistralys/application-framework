<?php

declare(strict_types=1);

namespace Application\Feedback;

use DBHelper_BaseFilterSettings;

class FeedbackFilterSettings extends DBHelper_BaseFilterSettings
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
