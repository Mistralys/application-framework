<?php

declare(strict_types=1);

use AppUtils\NamedClosure;

abstract class Application_EventHandler_OfflineEvents_OfflineListener
{
    /**
     * @var NamedClosure|null
     */
    private $callable = null;

    public function getCallable() : NamedClosure
    {
        if(!isset($this->callable))
        {
            $this->callable = $this->wakeUp();
        }

        return $this->callable;
    }

    abstract protected function wakeUp() : NamedClosure;
}
