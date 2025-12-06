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
 * @method IntegerCollectionItemInterface createStubRecord()
 */
interface IntegerCollectionInterface extends BaseCollectionInterface
{
    /**
     * @param int $record_id
     * @return bool
     */
    public function idExists(int $record_id): bool;

    /**
     * @param int $record_id
     * @return IntegerCollectionItemInterface
     */
    public function getByID(int $record_id): IntegerCollectionItemInterface;
}
