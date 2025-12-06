<?php

declare(strict_types=1);

namespace AppFrameworkTests\Eventables;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestApplication\TestEventableNamespaced;

final class NamespaceTest extends ApplicationTestCase
{
    /**
     * When changing the namespace of events, the listeners
     * must be namespace-specific.
     */
    public function test_listenersAreNamespaced(): void
    {
        $eventable = new TestEventableNamespaced('ns1');

        $eventable->addEventListener('SomeEvent', 'trim');
        $listeners = $eventable->getEventListeners('SomeEvent');

        $this->assertSame(1, $eventable->countEventListeners('SomeEvent'));
        $this->assertCount(1, $listeners);

        $eventable->setNamespace('ns2');

        $this->assertEmpty($eventable->getEventListeners('SomeEvent'));
        $this->assertSame(0, $eventable->countEventListeners('SomeEvent'));
    }
}
