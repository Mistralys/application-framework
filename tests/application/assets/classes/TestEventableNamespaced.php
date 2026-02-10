<?php

declare(strict_types=1);

namespace TestApplication;

use Application\EventHandler\Eventables\EventableInterface;
use Application\EventHandler\Eventables\EventableTrait;
use Application_Traits_Loggable;

class TestEventableNamespaced implements EventableInterface
{
    use EventableTrait;
    use Application_Traits_Loggable;

    private string $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getLogIdentifier(): string
    {
        return 'TestEventableNS[' . $this->namespace . ']';
    }

    public function getEventNamespace(string $eventName): string
    {
        return $this->namespace;
    }
}
