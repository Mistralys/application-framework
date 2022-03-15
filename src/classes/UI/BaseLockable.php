<?php

abstract class UI_BaseLockable implements Application_LockableItem_Interface
{
    protected bool $lockable = false;

    protected bool $locked = false;

    /**
     * @var string
     */
    protected string $lockReason = '';

    public function isLockable() : bool
    {
        return $this->lockable;
    }
    

    /**
     * @return $this
     */
    public function lock(string $reason) : self
    {
        if($this->isLockable()) {
            $this->locked = true;
            $this->lockReason = $reason;
        }
    
        return $this;
    }
    
    public function getLockReason() : string
    {
        if($this->locked) {
            return $this->lockReason;
        }
        
        return '';
    }
    
    public function unlock() : self
    {
        $this->locked = false;
        return $this;
    }
    
    public function isLocked() : bool
    {
        return $this->locked;
    }
    
    /**
     * Makes the button lockable: it will automatically be disabled
     * if the administration screen is locked by the lockmanager.
     *
     * @param bool $lockable
     * @return $this
     */
    public function makeLockable(bool $lockable=true) : self
    {
        $this->lockable = true;
        return $this;
    }
}
