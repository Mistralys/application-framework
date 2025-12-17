<?php

declare(strict_types=1);

namespace AppFrameworkTests\SessionTests;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Session\Events\SessionInstantiatedEvent;
use TestDriver\OfflineEvents\SessionInstantiated\TestSessionInstantiatedListener;

/**
 * Ensures that the expected session events have been triggered.
 * In CLI mode (during testing), these events are also triggered,
 * even if authentication is disabled. The user is the system user.
 *
 * To listen to the session events, the listeners are registered
 * using the offline event {@see SessionInstantiatedEvent}.
 *
 * @see Application_Bootstrap_Screen::triggerSessionInstantiated()
 * @see SessionInstantiatedEvent
 * @see TestSessionInstantiatedListener
 */
final class EventTest extends ApplicationTestCase
{
    public function test_frameworkSessionInstantiatedEventCalled() : void
    {
        $this->assertTrue(boot_defined(TestSessionInstantiatedListener::CONSTANT_INSTANTIATED));
    }

    public function test_frameworkSessionStartedEventCalled() : void
    {
        $this->assertTrue(boot_defined(TestSessionInstantiatedListener::CONSTANT_STARTED));
    }

    public function test_frameworkUserAuthenticatedEventCalled() : void
    {
        $this->assertTrue(boot_defined(TestSessionInstantiatedListener::CONSTANT_AUTHENTICATED));
    }
}
