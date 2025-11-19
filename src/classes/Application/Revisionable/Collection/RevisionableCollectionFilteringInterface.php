<?php
/**
 * @package Revisionables
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface;

/**
 * Interface for revisionable record collections for use
 * with revisionable filter criteria, {@see BaseRevisionableFilterCriteria}.
 *
 * This uses only the minimum methods needed by the filter
 * criteria, instead of the full collection interface ({@see RevisionableCollectionInterface}).
 * This allows for more flexible implementations of revisionable
 * collections that can still work with the filter criteria.
 *
 * @package Revisionables
 * @subpackage Collection
 */
interface RevisionableCollectionFilteringInterface extends IntegerCollectionFilteringInterface
{
    public function getRevisionsTableName() : string;
    public function getCurrentRevisionsTableName() : string;
    public function getRevisionKeyName() : string;
}
