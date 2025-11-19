<?php
/**
 * @package DBHelper
 * @subpackage FilterCriteria
 */

declare(strict_types=1);

namespace DBHelper\BaseFilterCriteria;

use Application\Collection\BaseCollectionInterface;

/**
 * Interface for collections that provide filtering capabilities
 * via filter criteria classes. It exposes methods needed by the
 * filter criteria to interact with the collection.
 *
 * > NOTE: This is the base interface. Use the type-specific
 * > interfaces that extend this one for your collections:
 * >
 * > - {@see IntegerCollectionFilteringInterface}
 * > - {@see StringCollectionFilteringInterface}
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface BaseCollectionFilteringInterface extends BaseCollectionInterface
{
    public function getRecordPrimaryName() : string;
    public function getRecordTableName() : string;
    public function getForeignKeys() : array;
    public function getRecordSearchableKeys() : array;
    public function getRecordDefaultSortKey() : string;
    public function getRecordDefaultSortDir() : string;
}
