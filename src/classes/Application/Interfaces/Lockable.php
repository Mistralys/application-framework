<?php
/**
 * File containing the interface {@link Application_Lockable_Interface}.
 *
 * @package Application
 * @subpackage LockManager
 * @see Application_Lockable_Interface
 */

/**
 * Base interface for lockable items, to access the
 * locked state of the item.
 *
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_Lockable_Interface
{
    /**
     * Whether the item is locked.
     * @return boolean
     */
    public function isLocked() : bool;

    /**
     * Whether the item is lockable.
     * @return boolean
     */
    public function isLockable() : bool;

    /**
     * Retrieves the human-readable reason for which this item
     * has been locked, if it is locked.
     *
     * @return string
     */
    public function getLockReason() : string;
}
