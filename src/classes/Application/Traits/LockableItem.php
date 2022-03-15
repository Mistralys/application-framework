<?php

trait Application_Traits_LockableItem
{
    protected bool $lockable = false;

    public function isLockable() : bool
    {
        return $this->lockable;
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
