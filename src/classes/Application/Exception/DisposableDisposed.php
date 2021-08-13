<?php

declare(strict_types=1);

class Application_Exception_DisposableDisposed extends Application_Exception
{
    const ERROR_DISPOSABLE_DISPOSED = 92101;

    public function __construct(Application_Interfaces_Disposable $disposable, $actionLabel)
    {
        parent::__construct(
            'Object has been disposed',
            sprintf(
                'Tried to do action [%s] on disposable [%s].',
                $actionLabel,
                $disposable->getIdentification()
            ),
            self::ERROR_DISPOSABLE_DISPOSED
        );
    }
}