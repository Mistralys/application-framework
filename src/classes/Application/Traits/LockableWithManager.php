<?php

trait Application_Traits_LockableWithManager
{
    /**
     * @var Application_LockManager|NULL
     */
    protected ?Application_LockManager $lockManager = null;

    /**
     * @return $this
     */
    public function setLockManager(Application_LockManager $lockManager) : self
    {
        $this->log('Using a lock manager: enabling locking.');
        $this->lockManager = $lockManager;
        return $this;
    }

    public function isLockable() : bool
    {
        return true;
    }

    public function isLocked() : bool
    {
        if(isset($this->lockManager)) {
            return $this->lockManager->isLocked();
        }

        return false;
    }

    public function getLockReason() : string
    {
        if(isset($this->lockManager)) {
            return (string)$this->lockManager->getLockReason();
        }

        return '';
    }

    public function getLockManager() : ?Application_LockManager
    {
        return $this->lockManager;
    }
}
