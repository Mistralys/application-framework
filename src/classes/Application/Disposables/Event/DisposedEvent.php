<?php

declare(strict_types=1);

namespace Application\Disposables\Event;

use Application\Disposables\DisposableInterface;
use Application\EventHandler\Eventables\BaseEventableEvent;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

class DisposedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'Disposed';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

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
