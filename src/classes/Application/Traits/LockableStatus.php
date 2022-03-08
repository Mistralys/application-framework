<?php

trait Application_Traits_LockableStatus
{
    protected bool $locked = false;
    protected string $lockReason = '';

    /**
     * @param string $reason
     * @return $this
     */
    public function lock(string $reason) : self
    {
        if($this->isLockable())
        {
            $this->locked = true;
            $this->lockReason = $reason;
        }

        return $this;
    }

    public function getLockReason() : string
    {
        if($this->isLocked())
        {
            return $this->lockReason;
        }

        return '';
    }

    /**
     * @return $this
     */
    public function unlock() : self
    {
        $this->locked = false;
        return $this;
    }

    public function isLocked() : bool
    {
        return $this->locked;
    }
}
