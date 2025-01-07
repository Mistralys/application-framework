<?php
/**
 * @package Application
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Collection;

/**
 * @package Application
 * @subpackage Collection
 */
interface IntegerCollectionItemInterface extends CollectionItemInterface
{
    public function getID() : int;
}
