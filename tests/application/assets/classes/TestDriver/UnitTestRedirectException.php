<?php

declare(strict_types=1);

namespace TestDriver;

use Application_Exception;

class UnitTestRedirectException extends Application_Exception
{
    public function __construct()
    {
        parent::__construct(
            'Redirect exception when in unit tests.'
        );
    }
}
