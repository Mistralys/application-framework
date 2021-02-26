<?php

abstract class UI_Page_Sidebar_LockableItem extends UI_Page_Sidebar_Item implements Application_LockableItem_Interface
{
    public function isLockable()
    {
        return $this->lockable;
    }
    
    protected $locked = false;
    
    protected $lockReason = null;
    
   /**
    * {@inheritDoc}
    * @see Application_LockableItem_Interface::lock()
    * @return UI_Page_Sidebar_LockableItem
    */
    public function lock($reason)
    {
        if($this->isLockable()) {
            $this->locked = true;
            $this->lockReason = $reason;
        }
        
        return $this;
    }
    
    public function getLockReason()
    {
        if($this->locked) {
            return $this->lockReason;
        }
        
        return '';
    }
    
   /**
    * {@inheritDoc}
    * @see Application_LockableItem_Interface::unlock()
    * @return UI_Page_Sidebar_LockableItem
    */
    public function unlock()
    {
        $this->locked = false;
        return $this;
    }
    
    public function isLocked()
    {
        return $this->locked;
    }

    protected $lockable = false;
    
   /**
    * Makes the button lockable: it will automatically be disabled
    * if the administration screen is locked by the lockmanager.
    * 
    * @param bool $lockable
    * @return UI_Page_Sidebar_LockableItem
    */
    public function makeLockable($lockable=true)
    {
        $this->lockable = true;
        return $this;
    }
}
