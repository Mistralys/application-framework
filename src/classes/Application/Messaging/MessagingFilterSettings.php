<?php

declare(strict_types=1);

namespace Application\Messaging;

use DBHelper_BaseFilterSettings;

/**
 * @property MessagingCollection $collection
 */
class MessagingFilterSettings extends DBHelper_BaseFilterSettings
{
    const string SETTING_SEARCH = 'search';

    public function __construct(MessagingCollection $collection)
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
