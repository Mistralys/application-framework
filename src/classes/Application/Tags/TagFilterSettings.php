<?php

declare(strict_types=1);

namespace Application\Tags;

use DBHelper_BaseFilterSettings;

class TagFilterSettings extends DBHelper_BaseFilterSettings
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
