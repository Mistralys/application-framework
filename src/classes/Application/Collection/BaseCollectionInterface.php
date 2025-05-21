<?php
/**
 * @package Application
 * @subpackage Collection
 */

namespace Application\Collection;

use Application\Interfaces\FilterCriteriaInterface;
use Application_Interfaces_Disposable;

/**
 * @package Application
 * @subpackage Collection
 */
interface BaseCollectionInterface extends Application_Interfaces_Disposable
{
    /**
     * @return FilterCriteriaInterface
     */
    public function getFilterCriteria();

    /**
     * Checks whether the specified collection record ID exists.
     * @param int|string|NULL $record_id
     * @return boolean
     */
    public function idExists($record_id): bool;

    /**
     * @return CollectionItemInterface
     */
    public function createDummyRecord();

    /**
     * Retrieves all available collection records.
     * @return CollectionItemInterface[]
     */
    public function getAll(): array;

    /**
     * Retrieves a collection record by its primary key.
     * @param int|string $record_id
     * @return CollectionItemInterface
     */
    public function getByID($record_id): CollectionItemInterface;
}
