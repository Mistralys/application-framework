<?php

declare(strict_types=1);

namespace Application\Collection;

/**
 * @package Application
 * @subpackage Collection
 *
 * @method StringCollectionItemInterface[] getAll()
 * @method StringCollectionItemInterface createDummyRecord()
 */
interface StringCollectionInterface extends BaseCollectionInterface
{
    /**
     * @param int|string $record_id
     * @return bool
     */
    public function idExists($record_id): bool;

    /**
     * @param int|string $record_id
     * @return StringCollectionItemInterface
     */
    public function getByID($record_id): StringCollectionItemInterface;
}
