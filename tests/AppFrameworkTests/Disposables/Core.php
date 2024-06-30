<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Exception\DisposableDisposedException;

class Disposables_CoreTests extends ApplicationTestCase
{
    private bool $eventTriggered = false;
    private bool $disposedTriggered;

    protected function setUp() : void
    {
        parent::setUp();

        $this->disposedTriggered = false;
        $this->eventTriggered = false;
    }

    /**
     * When a disposable has been disposed, no new events
     * are allowed to be triggered.
     */
    public function test_disableEvents() : void
    {
        $disposable = new TestDisposable();
        $disposable->onEventTriggered(array($this, 'callback_eventTriggered'));

        $disposable->triggerTheEvent();
        $this->assertTrue($this->eventTriggered);

        $this->eventTriggered = false;

        $disposable->dispose();

        $disposable->triggerTheEvent();
        $this->assertFalse($this->eventTriggered);
    }

    public function test_isDisposedTrueAfterDisposing() : void
    {
        $disposable = new TestDisposable();
        $disposable->dispose();

        $this->assertTrue($disposable->isDisposed());
    }

    /**
     * On disposing, all children must also be disposed.
     */
    public function test_disposeChildren() : void
    {
        $disposable = new TestDisposable();
        $disposable->addChildren(3);

        $children = $disposable->getChildDisposables();

        $this->assertCount(3, $children);

        $disposable->dispose();

        foreach($children as $child)
        {
            $this->assertTrue($child->isDisposed());
        }
    }

    /**
     * The _dispose() method must be called on disposing.
     */
    public function test_cleanup() : void
    {
        $disposable = new TestDisposable();

        $this->assertFalse($disposable->isCleaned());

        $disposable->dispose();

        $this->assertTrue($disposable->isCleaned());
    }

    /**
     * Trying to execute an action that is not allowed after
     * disposing must trigger an exception.
     */
    public function test_requireNotDisposed() : void
    {
        $disposable = new TestDisposable();
        $disposable->dispose();

        $this->expectException(DisposableDisposedException::class);

        $disposable->notAllowedAfterDisposing();
    }

    /**
     * Disposing must trigger the disposed event.
     */
    public function test_disposedEvent() : void
    {
        $disposable = new TestDisposable();
        $disposable->onDisposed(array($this, 'callback_disposed'));

        $disposable->dispose();

        $this->assertTrue($this->disposedTriggered);
    }

    public function callback_disposed() : void
    {
        $this->disposedTriggered = true;
    }

    public function callback_eventTriggered() : void
    {
        $this->eventTriggered = true;
    }
}
