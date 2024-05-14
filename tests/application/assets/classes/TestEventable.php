<?php

declare(strict_types=1);

namespace TestApplication;

use Application_Interfaces_Eventable;
use Application_Traits_Eventable;
use Application_Traits_Loggable;

class TestEventable implements Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public function getLogIdentifier(): string
    {
        return 'TestEventable';
    }
}
