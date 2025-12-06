<?php
/**
 * @package API
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use DBHelper_BaseFilterSettings;

/**
 * @package API
 * @subpackage API Keys
 */
class APIKeyFilterSettings extends DBHelper_BaseFilterSettings
{
    public const string SETTING_SEARCH = 'search';

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);
    }

    protected function _configureFilters(): void
    {
    }
}
