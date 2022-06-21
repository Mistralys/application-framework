<?php

declare(strict_types=1);

use Application\ClassFinder;
use Application\Exception\ClassNotExistsException;
use Application\Exception\UnexpectedInstanceException;

class Application_Traits_Disposable_Event_Disposed extends Application_EventHandler_EventableEvent
{
    /**
     * @return Application_Interfaces_Disposable
     *
     * @throws ClassNotExistsException
     * @throws UnexpectedInstanceException
     */
    public function getDisposable() : Application_Interfaces_Disposable
    {
        return ClassFinder::requireInstanceOf(
            Application_Interfaces_Disposable::class,
            $this->getArgument(0)
        );
    }
}
