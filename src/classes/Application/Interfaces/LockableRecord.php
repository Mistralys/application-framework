<?php
/**
 * File containing the interface {@link Application_LockableRecord_Interface}.
 *
 * @package Application
 * @subpackage LockManager
 * @see Application_LockableRecord_Interface
 */

/**
 * Interface for lockable records: data containers like
 * revisionables that support being locked using the lock
 * manager.
 *
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_LockableRecord_Interface extends Application_Lockable_Interface
{
    /**
     * Sets the lock manager instance that is used to handle
     * locking of this record.
     *
     * @param Application_LockManager $lockManager
     * @return $this
     */
    public function setLockManager(Application_LockManager $lockManager) : self;

    /**
     * Retrieves the lock manager instance that is used
     * to handle the item's locking state. Note that
     * this may be null.
     *
     * @return Application_LockManager|NULL
     */
    public function getLockManager() : ?Application_LockManager;

    /**
     * Retrieves the primary key that is used to recognize
     * this item's locking state.
     *
     * @return string
     */
    public function getLockPrimary() : string;

    /**
     * Whether this lockable item is editable.
     *
     * @return bool
     */
    public function isEditable() : bool;

    /**
     * Retrieves a human-readable label of the record.
     *
     * @return string
     */
    public function getLabel() : string;
}
