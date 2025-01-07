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
 *
 * @method IntegerCollectionItemInterface[] getAll()
 * @method IntegerCollectionItemInterface createDummyRecord()
 */
interface IntegerCollectionInterface extends BaseCollectionInterface
{
    /**
     * @param int|string|NULL $record_id
     * @return bool
     */
    public function idExists($record_id): bool;

    /**
     * @param int|string $record_id
     * @return IntegerCollectionItemInterface
     */
    public function getByID($record_id): IntegerCollectionItemInterface;
}
