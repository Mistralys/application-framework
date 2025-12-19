<?php

declare(strict_types=1);

namespace Application\AppSets;

use DBHelper_BaseFilterSettings;

class AppSetsFilterSettings extends DBHelper_BaseFilterSettings
{
    public const string SETTING_SEARCH = 'search';

    public function __construct(AppSetsCollection $collection)
    {
        parent::__construct($collection);
    }

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
        $this->configureSearch(self::SETTING_SEARCH);
    }
}
