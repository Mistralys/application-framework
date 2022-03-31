<?php

declare(strict_types=1);

namespace Application\Exception;

use Application;
use Application_Exception;

class ClassNotExistsException extends Application_Exception
{
    public function __construct(string $className)
    {
        parent::__construct(
            'Class does not exist',
            sprintf(
                'The class [%s] cannot be auto-loaded.',
                $className
            ),
            Application::ERROR_CLASS_NOT_FOUND
        );
    }
}
