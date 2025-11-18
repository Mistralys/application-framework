<?php
/**
 * @package Ratings
 * @subpackage Screens
 */

declare(strict_types=1);

namespace Application\Ratings\Screens;

use DBHelper_BaseFilterSettings;

/**
 * @package Ratings
 * @subpackage Screens
 *
 * @property RatingScreensCollection $collection
 */
class RatingScreensFilterSettings extends DBHelper_BaseFilterSettings
{
    public const string SETTING_SEARCH = 'search';

    public function __construct(RatingScreensCollection $collection)
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
