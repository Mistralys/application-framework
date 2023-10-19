<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;

class Revisionables_EventsTests extends ApplicationTestCase
{

    // region: _Tests

    /**
     * Events must be revision-specific, so they are only
     * executed when the revision is active.
     */
    public function test_revisionSpecific() : void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->triggerTheEvent();

        $this->assertTrue($this->revision1Event);
        $this->assertFalse($this->revision2Event);

        $this->resetEvents();

        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->triggerTheEvent();

        $this->assertFalse($this->revision1Event);
        $this->assertTrue($this->revision2Event);
    }

    /**
     * Ignoring events by name must be revision specific.
     */
    public function test_ignoreRevisionSpecific() : void
    {
        $this->createRevisionable();

        // Ignore the event in revision 1
        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->ignoreTestEvent();
        $this->revisionable->triggerTheEvent();

        // Trigger it for revision 2
        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->triggerTheEvent();

        $this->assertFalse($this->revision1Event);
        $this->assertTrue($this->revision2Event);
    }

    /**
     * If the event to ignore is not revision specific,
     * it must be ignored independently of the selected revision.
     */
    public function test_ignoreNonRevisionSpecific() : void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);

        $this->revisionable->onRevisionAdded(array($this, 'callback_revisionAdded'));
        $this->revisionable->ignoreRevisionAddedEvent();
        $this->revisionable->createRevision();

        $this->assertNull($this->revisionAdded);
    }

    /**
     * Some events are not revision specific. These must
     * be triggered regardless of the selected revision.
     */
    public function test_revisionlessEvent() : void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->onRevisionAdded(array($this, 'callback_revisionAdded'));
        $this->revisionable->selectRevision($this->rev2ID);

        $this->assertNull($this->revisionAdded);

        $this->revisionable->createRevision();

        $this->assertInstanceOf(Application_Revisionable_Event_RevisionAdded::class, $this->revisionAdded);
    }

    // endregion

    // region: Support methods

    /**
     * @var bool
     */
    private $revision1Event = false;

    /**
     * @var bool
     */
    private $revision2Event = false;

    /**
     * @var TestRevisionableMemory
     */
    private $revisionable;

    /**
     * @var int
     */
    private $rev1ID;

    /**
     * @var int
     */
    private $rev2ID;

    /**
     * @var Application_Revisionable_Event_RevisionAdded|NULL
     */
    private $revisionAdded;

    protected function setUp() : void
    {
        parent::setUp();

        $this->resetEvents();
    }

    /**
     * @return TestRevisionableMemory
     */
    private function createRevisionable() : TestRevisionableMemory
    {
        $this->revisionable = new TestRevisionableMemory();

        $this->assertSame(0, $this->revisionable->countRevisions());

        $this->rev1ID = $this->revisionable->createRevision();
        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->onTriggerEvent(array($this, 'callback_revision1'));

        $this->rev2ID = $this->revisionable->createRevision();
        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->onTriggerEvent(array($this, 'callback_revision2'));

        $this->assertSame(2, $this->revisionable->countRevisions());

        return $this->revisionable;
    }

    public function callback_revisionAdded(Application_Revisionable_Interface $revisionable, Application_Revisionable_Event_RevisionAdded $event) : void
    {
        $this->revisionAdded = $event;
    }

    public function callback_revision1() : void
    {
        $this->revision1Event = true;
    }

    public function callback_revision2() : void
    {
        $this->revision2Event = true;
    }

    private function resetEvents() : void
    {
        $this->revision1Event = false;
        $this->revision2Event = false;
        $this->revisionAdded = null;
    }

    // endregion
}
