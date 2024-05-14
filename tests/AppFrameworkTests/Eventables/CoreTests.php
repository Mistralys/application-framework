<?php

declare(strict_types=1);

namespace AppFrameworkTests\Eventables;

use AppFrameworkTestClasses\ApplicationTestCase;
use TestApplication\TestEventable;

final class CoreTests extends ApplicationTestCase
{
    public function test_removeListener(): void
    {
        $eventable = new TestEventable();

        $l1 = $eventable->addEventListener('SomeEvent', 'trim');
        $l2 = $eventable->addEventListener('SomeEvent', 'ltrim');
        $l3 = $eventable->addEventListener('SomeEvent', 'rtrim');

        $this->assertSame(3, $eventable->countEventListeners('SomeEvent'));

        $eventable->removeEventListener($l1);

        $listeners = $eventable->getEventListeners('SomeEvent');

        $this->assertCount(2, $listeners);

        foreach ($listeners as $listener) {
            $this->assertNotEquals($l1->getID(), $listener->getID(), 'The removed listener must not be in the list anymore.');
        }
    }
}
