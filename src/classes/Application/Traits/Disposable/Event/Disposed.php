<?php

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

class Application_Traits_Disposable_Event_Disposed extends Application_EventHandler_EventableEvent
{
    /**
     * @return Application_Interfaces_Disposable
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function getDisposable() : Application_Interfaces_Disposable
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Interfaces_Disposable::class,
            $this->getArgument(0)
        );
    }
}
