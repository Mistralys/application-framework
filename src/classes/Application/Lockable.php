<?php
/**
 * File containing the {@link Application_LockableItem_Interface} and {@link Application_LockableRecord_Interface} interfaces.
 * 
 * @package Application
 * @subpackage LockManager
 * @see Application_LockableItem_Interface
 * @see Application_LockableRecord_Interface
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
    public function isLocked();
    
   /**
    * Whether the item is lockable.
    * @return boolean
    */
    public function isLockable();

   /**
    * Retrieves the human readable reason for which this item
    * has been locked, if it is locked.
    * 
    * @return string
    */
    public function getLockReason();
}

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
    * {@link lock()} method. Otherwise trying to
    * lock the item will not work.
    * 
    * @param bool $lockable
    * @return Application_LockableItem_Interface
    */
    public function makeLockable($lockable=true);
    
   /**
    * Whether the item is lockable.
    * @return boolean
    */
    public function isLockable();
    
   /**
    * Locks the item. Only works if the item
    * has been set as lockable beforehand.
    * 
    * @param string $reason Human readable reason why the item has been locked.
    * @return Application_LockableItem_Interface
    */
    public function lock($reason);
    
   /**
    * Unlocks the item.
    * @return Application_LockableItem_Interface
    */
    public function unlock();
}

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
    * @return Application_LockableRecord_Interface
    */
    public function setLockManager(Application_LockManager $lockManager);
    
   /**
    * Retrieves the lock manager instance that is used
    * to handle the item's locking state. Note that
    * this may be null.
    * 
    * @return Application_LockManager|NULL
    */
    public function getLockManager();
    
   /**
    * Retrieves the primary key that is used to recognize
    * this item's locking state. Can be any string or number.
    * 
    * @return integer|string
    */
    public function getLockPrimary();
    
    /**
     * Whether this lockable item is editable.
     * 
     * @return bool
     */
    public function isEditable();
    
   /**
    * Retrieves a human readable label of the record.
    * 
    * @return string
    */
    public function getLabel();
}