<?php

declare(strict_types=1);

namespace Application\Disposables\Event;

use Application\Disposables\DisposableInterface;
use Application_EventHandler_EventableEvent;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

class DisposedEvent extends Application_EventHandler_EventableEvent
{
    /**
     * @return DisposableInterface
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function getDisposable(): DisposableInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            DisposableInterface::class,
            $this->getArgument(0)
        );
    }
}
