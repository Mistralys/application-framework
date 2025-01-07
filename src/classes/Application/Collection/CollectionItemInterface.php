<?php
/**
 * @package Application
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Collection;

/**
 * Interface for collection items.
 *
 * @package Application
 * @subpackage Collection
 */
interface CollectionItemInterface
{
    /**
     * @return int|string
     */
    public function getID();

    /**
     * @return string
     */
    public function getLabel(): string;
}
