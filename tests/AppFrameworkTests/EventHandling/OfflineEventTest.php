<?php
/**
 * @package Framework Tests
 * @subpackage Offline Events
 */

declare(strict_types=1);

namespace AppFrameworkTests\EventHandling;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use application\assets\classes\TestDriver\OfflineEvents\PriorityTest\PriorityListenerC;
use TestDriver\OfflineEvents\PriorityTest\PriorityListenerA;
use TestDriver\OfflineEvents\PriorityTest\PriorityListenerB;
use TestDriver\OfflineEvents\PriorityTestEvent;
use TestDriver\OfflineEvents\Test\ListenerA;
use TestDriver\OfflineEvents\Test\ListenerB;
use TestDriver\OfflineEvents\TestEvent;

/**
 * @package Framework Tests
 * @subpackage Offline Events
 *
 * @see TestEvent
 * @see ListenerA
 * @see ListenerB
 *
 * @see PriorityTestEvent
 * @see PriorityListenerA
 * @see PriorityListenerB
 * @see PriorityListenerC
 */
final class OfflineEventTest extends ApplicationTestCase
{
    public function test_createEventWithoutClassName(): void
    {
        AppFactory::createOfflineEvents()
            ->createEvent(
                TestEvent::EVENT_NAME,
                array('argument')
            );

        $this->addToAssertionCount(1);
    }

    public function test_createEventWithClassName(): void
    {
        AppFactory::createOfflineEvents()
            ->createEvent(
                TestEvent::EVENT_NAME,
                array('argument'),
                TestEvent::class
            );

        $this->addToAssertionCount(1);
    }

    public function test_eventHasExpectedListeners(): void
    {
        $event = AppFactory::createOfflineEvents()->createEvent(TestEvent::EVENT_NAME, array('argument'));

        $this->assertNotNull($event->getEventClass());
        $this->assertNotEmpty($event->getListenerFolders());
        $this->assertNotEmpty($event->getListeners());
    }

    public function test_triggerEvent(): void
    {
        $offline = AppFactory::createOfflineEvents();

        $event = $offline->triggerEvent(TestEvent::EVENT_NAME, array('argument'));

        $this->assertNotNull($event->getTriggeredEvent());
        $this->assertTrue(boot_defined(ListenerA::CONSTANT_NAME));
        $this->assertTrue(boot_defined(ListenerB::CONSTANT_NAME));
        $this->assertEquals('argument', boot_constant(ListenerB::CONSTANT_NAME));
    }

    /**
     * When no priority is set, the listeners are ordered by their
     * class name ascending, to follow the file naming.
     */
    public function test_defaultOrderingWithoutPriority() : void
    {
        $listeners = AppFactory::createOfflineEvents()
            ->createEvent(TestEvent::EVENT_NAME, array('priority-argument'))
            ->getListeners();

        $this->assertCount(2, $listeners);
        $this->assertInstanceOf(ListenerA::class, $listeners[0]);
        $this->assertInstanceOf(ListenerB::class, $listeners[1]);
    }

    /**
     * The method {@see \Application_EventHandler_OfflineEvents_OfflineListener::getPriority()}
     * is used to adjust the priority in which the listeners are executed.
     * In the test event below, the listeners are all ordered by priority.
     */
    public function test_orderingWithPriority() : void
    {
        $listeners = AppFactory::createOfflineEvents()
            ->triggerEvent(PriorityTestEvent::EVENT_NAME)
            ->getListeners();

        $this->assertCount(3, $listeners);
        $this->assertInstanceOf(PriorityListenerB::class, $listeners[0]);
        $this->assertInstanceOf(PriorityListenerC::class, $listeners[1]);
        $this->assertInstanceOf(PriorityListenerA::class, $listeners[2]);
    }
}
