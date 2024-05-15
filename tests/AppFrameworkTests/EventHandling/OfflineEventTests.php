<?php

declare(strict_types=1);

namespace AppFrameworkTests\EventHandling;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use TestDriver\OfflineEvents\TestEvent;
use TestDriver\OfflineEvents\TestEvent\ListenerA;
use TestDriver\OfflineEvents\TestEvent\ListenerB;

/**
 * @see TestEvent
 * @see ListenerA
 * @see ListenerB
 */
final class OfflineEventTests extends ApplicationTestCase
{
    public function test_createEventWithoutClassName(): void
    {
        $this->assertNotNull(AppFactory::createOfflineEvents()
            ->createEvent(
                TestEvent::EVENT_NAME,
                array('argument')
            ));
    }

    public function test_createEventWithClassName(): void
    {
        $this->assertNotNull(AppFactory::createOfflineEvents()
            ->createEvent(
                TestEvent::EVENT_NAME,
                array('argument'),
                TestEvent::class
            ));
    }

    public function test_eventHasExpectedListeners(): void
    {
        $event = AppFactory::createOfflineEvents()->createEvent(TestEvent::EVENT_NAME, array('argument'));

        $this->assertNotNull($event);
        $this->assertNotNull($event->getEventClass());
        $this->assertNotEmpty($event->getListenerFolders());
        $this->assertNotEmpty($event->getListeners());
    }

    public function test_triggerEvent(): void
    {
        $offline = AppFactory::createOfflineEvents();

        $event = $offline->triggerEvent(TestEvent::EVENT_NAME, array('argument'));

        $this->assertNotNull($event);
        $this->assertNotNull($event->getTriggeredEvent());
        $this->assertTrue(defined(ListenerA::CONSTANT_NAME));
        $this->assertTrue(defined(ListenerB::CONSTANT_NAME));
        $this->assertEquals('argument', constant(ListenerB::CONSTANT_NAME));
    }
}
