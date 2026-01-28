<?php

declare(strict_types=1);

namespace TestApplication;

use Application\EventHandler\Eventables\EventableInterface;
use Application\EventHandler\Eventables\EventableTrait;
use Application_Traits_Loggable;

class TestEventable implements EventableInterface
{
    use EventableTrait;
    use Application_Traits_Loggable;

    public function getLogIdentifier(): string
    {
        return 'TestEventable';
    }
}
