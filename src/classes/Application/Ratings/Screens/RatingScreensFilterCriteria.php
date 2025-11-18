<?php
/**
 * @package Ratings
 * @subpackage Screens
 */

declare(strict_types=1);

namespace Application\Ratings\Screens;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @package Ratings
 * @subpackage Screens
 *
 * @property RatingScreensCollection $collection
 */
class RatingScreensFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public function __construct(RatingScreensCollection $collection)
    {
        parent::__construct($collection);
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
