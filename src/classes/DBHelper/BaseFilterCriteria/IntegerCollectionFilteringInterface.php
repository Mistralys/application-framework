<?php
/**
 * @package DBHelper
 * @subpackage FilterCriteria
 */

declare(strict_types=1);

namespace DBHelper\BaseFilterCriteria;

use Application\Collection\IntegerCollectionInterface;

/**
 * Interface for integer-based collections that provide filtering
 * capabilities via filter criteria classes.
 *
 * > NOTE: This does not add any own methods. It brings together
 * > the base filter collection interface and the integer collection
 * > interface for type safety.
 *
 * @package DBHelper
 * @subpackage FilterCriteria
 */
interface IntegerCollectionFilteringInterface extends BaseCollectionFilteringInterface, IntegerCollectionInterface
{
}
