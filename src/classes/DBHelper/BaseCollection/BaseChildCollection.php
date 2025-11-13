<?php
/**
 * @package DBHelper
 * @subpackage Base Collection
 */

declare(strict_types=1);

namespace DBHelper\BaseCollection;

use DBHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection;
use DBHelper_Exception;

/**
 * DBHelper collection that requires a parent record to be bound
 * from another DBHelper collection.
 *
 * > NOTE: Child collections can be nested, i.e. a child collection
 * > can itself have further child collections.
 *
 * ## Usage
 *
 * 1. Extend this class, implement the abstract methods
 * 2. When creating the collection, specify the parent record in your {@see DBHelper::createCollection()} call.
 *
 * @package DBHelper
 * @subpackage Base Collection
 */
abstract class BaseChildCollection extends DBHelper_BaseCollection implements ChildCollectionInterface
{
    protected ?DBHelperRecordInterface $parentRecord = null;

    /**
     * This is only available if the collection has a parent collection.
     *
     * @return DBHelperRecordInterface
     */
    public function getParentRecord() : DBHelperRecordInterface
    {
        return $this->parentRecord;
    }

    final public function bindParentRecord(?DBHelperRecordInterface $record) : void
    {
        if($record === null) {
            throw new DBHelper_Exception(
                'No parent record specified',
                sprintf(
                    'The DBHelper collection class [%s] requires a parent record to be specified when calling createCollection.',
                    get_class($this)
                ),
                DBHelper::ERROR_NO_PARENT_RECORD_SPECIFIED
            );
        }

        if(isset($this->parentRecord)) {
            throw new DBHelper_Exception(
                'Record already bound',
                sprintf(
                    'Cannot bind record [%s, ID %s], already bound to record [%s, ID %s].',
                    get_class($record),
                    $record->getID(),
                    get_class($this->parentRecord),
                    $this->parentRecord->getID()
                ),
                DBHelperCollectionException::ERROR_COLLECTION_ALREADY_HAS_PARENT
            );
        }

        $parentClass = get_class($record->getCollection());
        if($parentClass !== $this->getParentCollectionClass()) {
            throw new DBHelper_Exception(
                'Invalid parent record',
                sprintf(
                    'The DBHelper collection class [%s] requires a parent record of the collection [%s], provided was a record of type [%s].',
                    get_class($this),
                    $this->getParentCollectionClass(),
                    get_class($record->getCollection())
                ),
                DBHelper::ERROR_INVALID_PARENT_RECORD
            );
        }

        $this->parentRecord = $record;

        $this->setForeignKey(
            $this->getParentRecord()->getCollection()->getRecordPrimaryName(),
            (string)$record->getID()
        );

        $this->parentRecord->onDisposed($this->callback_parentRecordDisposed(...));
    }

    /**
     * Fetch a fresh instance of the parent record of the
     * collection when that record instance has been disposed.
     * If the record does not exist anymore, no changes are
     * made - an exception will be thrown if the record is
     * accessed.
     */
    private function callback_parentRecordDisposed() : void
    {
        $collection = $this->parentRecord->getCollection();

        if($collection->idExists($this->parentRecord->getID()))
        {
            $this->parentRecord = $collection->getByID($this->parentRecord->getID());
            return;
        }

        $this->dispose();
    }

    public function getParentCollection() : DBHelperCollectionInterface
    {
        return $this->getParentRecord()->getCollection();
    }

    /**
     * @throws DBHelper_Exception
     * @see DBHelperCollectionException::ERROR_NO_PARENT_RECORD_BOUND
     */
    protected function checkRecordPrerequisites() : void
    {
        if($this->parentRecord !== null) {
            return;
        }

        throw new DBHelper_Exception(
            'No parent record bound',
            sprintf(
                'Collections of type [%s] need a parent record to be set.',
                get_class($this)
            ),
            DBHelperCollectionException::ERROR_NO_PARENT_RECORD_BOUND
        );
    }

    public function resetCollection(): self
    {
        parent::resetCollection();

        // Also refresh the parent record, in case that collection
        // has been reset as well.
        if(isset($this->parentRecord))
        {
            $this->parentRecord = $this->parentRecord->getCollection()->getByID($this->parentRecord->getID());
        }

        return $this;
    }
}
