<?php

declare(strict_types=1);

namespace Application\Collection;

/**
 * @package Application
 * @subpackage Collection
 *
 * @method StringCollectionItemInterface[] getAll()
 * @method StringCollectionItemInterface createStubRecord()
 */
interface StringCollectionInterface extends BaseCollectionInterface
{
    /**
     * @param string $record_id
     * @return bool
     */
    public function idExists(string $record_id): bool;

    /**
     * @param string $record_id
     * @return StringCollectionItemInterface
     */
    public function getByID(string $record_id): StringCollectionItemInterface;
}
