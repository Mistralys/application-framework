<?php
/**
 * @package Application
 * @subpackage Collection
 */

namespace Application\Collection;

use Application\Interfaces\FilterCriteriaInterface;
use Application\Revisionable\RevisionableInterface;
use Application\Disposables\DisposableInterface;

/**
 * Base interface for collections.
 *
 * > NOTE: Use the type-specific collection interfaces
 * > (e.g., {@see IntegerCollectionInterface}) instead
 * > of this one where possible.
 *
 * @package Application
 * @subpackage Collection
 *
 * @see IntegerCollectionInterface
 * @see StringCollectionInterface
 */
interface BaseCollectionInterface extends DisposableInterface
{
    public function getFilterCriteria() : FilterCriteriaInterface;

    /**
     * Creates a non-functional stub record to access information
     * that only instances can provide.
     *
     * @return RevisionableInterface
     */
    public function createStubRecord() : CollectionItemInterface;

    /**
     * Retrieves all available collection records.
     * @return CollectionItemInterface[]
     */
    public function getAll(): array;
}
