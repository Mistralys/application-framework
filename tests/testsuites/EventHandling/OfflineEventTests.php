<?php

declare(strict_types=1);

final class EventHandling_OfflineEventTests extends ApplicationTestCase
{
    /**
     * @see TestDriver_OfflineEvents_TestEvent_ListenerA
     * @see TestDriver_OfflineEvents_TestEvent_ListenerB
     */
    public function test_removeListener() : void
    {
        $offline = Application_EventHandler::createOfflineEvents();

        $this->assertNotNull($offline->createEvent('TestEvent', array('argument')));
    }

    public function test_trigger() : void
    {
        $offline = Application_EventHandler::createOfflineEvents();

        $event = $offline->triggerEvent('TestEvent', array('argument'));

        $this->assertNotNull($event->getTriggeredEvent());
        $this->assertTrue(defined('OFFLINE_EVENTS_LISTENER_A_TRIGGERED'));
        $this->assertEquals('argument', constant('OFFLINE_EVENTS_LISTENER_B_ARGUMENT'));
    }
}
