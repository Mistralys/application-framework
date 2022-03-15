<?php
/**
 * File containing the interface {@link Application_LockableItem_Interface}.
 *
 * @package Application
 * @subpackage LockManager
 * @see Application_LockableItem_Interface
 */

/**
 * Interface for lockable items, like UI elements
 * that support being shown as locked. By default
 * they are not lockable: they have to be made
 * lockable first with the <code>makeLockable()</code>
 * method.
 *
 * This allows having any number of items that support
 * locking, but only some that actually are, like
 * some buttons in a sidebar for example.
 *
 * @package Application
 * @subpackage LockManager
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_LockableItem_Interface extends Application_Lockable_Interface
{
    /**
     * Makes the item lockable, enabling the
     * {@link lock()} method. Otherwise, trying to
     * lock the item will not work.
     *
     * @param bool $lockable
     * @return $this
     */
    public function makeLockable(bool $lockable=true) : self;

    /**
     * Locks the item. Only works if the item
     * has been set as lockable beforehand.
     *
     * @param string $reason Human-readable reason why the item has been locked.
     * @return $this
     */
    public function lock(string $reason) : self;

    /**
     * Unlocks the item.
     * @return $this
     */
    public function unlock() : self;
}
