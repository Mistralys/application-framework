<?php

declare(strict_types=1);

namespace Application\API\Clients;

use DBHelper_BaseFilterSettings;

class APIClientFilterSettings extends DBHelper_BaseFilterSettings
{
    const string SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
    }
}
