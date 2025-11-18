<?php

declare(strict_types=1);

namespace AppFrameworkTests\Revisionables;

use Application\Revisionable\Event\RevisionAddedEvent;
use Mistralys\AppFrameworkTests\TestClasses\RevisionableTestCase;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\Revisionables\RevisionableRecord;

final class RevisionSpecificEventTests extends RevisionableTestCase
{

    // region: _Tests

    /**
     * Events must be revision-specific, so they are only
     * executed when the revision is active.
     */
    public function test_revisionSpecific(): void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->triggerTestEvent();

        $this->assertTrue($this->revision1Event);
        $this->assertFalse($this->revision2Event, 'Because we are on revision 1, revision 2 event must not have been triggered.');

        $this->resetEvents();

        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->triggerTestEvent();

        $this->assertFalse($this->revision1Event, 'Because we are on revision 2, revision 1 event must not have been triggered.');
        $this->assertTrue($this->revision2Event);
    }

    /**
     * Ignoring events by name must be revision-specific.
     */
    public function test_ignoreRevisionSpecific(): void
    {
        $this->createRevisionable();

        // Ignore the event in revision 1
        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->ignoreTestEvent();
        $this->revisionable->triggerTestEvent();

        // Trigger it for revision 2
        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->triggerTestEvent();

        $this->assertFalse($this->revision1Event);
        $this->assertTrue($this->revision2Event);
    }

    /**
     * If the event to ignore is not revision-specific,
     * it must be ignored independently of the selected revision.
     */
    public function test_ignoreNonRevisionSpecific(): void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);

        $this->revisionable->onRevisionAdded(array($this, 'callback_revisionAdded'));
        $this->revisionable->ignoreRevisionAddedEvent();
        $this->revisionable->createTestRevision();

        $this->assertNull($this->revisionAdded);
    }

    /**
     * Some events are not revision-specific. These must
     * be triggered regardless of the selected revision.
     */
    public function test_revisionlessEvent(): void
    {
        $this->createRevisionable();

        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->onRevisionAdded($this->callback_revisionAdded(...));
        $this->revisionable->selectRevision($this->rev2ID);

        $this->assertNull($this->revisionAdded);

        $this->revisionable->createTestRevision();

        $this->assertInstanceOf(RevisionAddedEvent::class, $this->revisionAdded);
    }

    // endregion

    // region: Support methods

    private bool $revision1Event = false;
    private bool $revision2Event = false;
    private RevisionableRecord $revisionable;
    private int $rev1ID;
    private int $rev2ID;
    private ?RevisionAddedEvent $revisionAdded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetEvents();
    }

    /**
     * @return void
     */
    private function createRevisionable(): void
    {
        $this->revisionable = RevisionableCollection::getInstance()->createNew('label', 'alias');

        $this->assertFalse(
            $this->revisionable->isEventRevisionAgnostic(RevisionableRecord::EVENT_TEST_EVENT),
            'The test event must be revision-specific.'
        );

        // Create two revisions, each with their own event handlers
        // that work so that the correct one is triggered based on
        // the selected revision.

        $this->rev1ID = $this->revisionable->createTestRevision();
        $this->revisionable->selectRevision($this->rev1ID);
        $this->revisionable->onTestEvent($this->callback_revision1(...));

        $this->rev2ID = $this->revisionable->createTestRevision();
        $this->revisionable->selectRevision($this->rev2ID);
        $this->revisionable->onTestEvent($this->callback_revision2(...));

        $this->assertSame(2, $this->revisionable->countRevisions());
    }

    public function callback_revisionAdded(RevisionAddedEvent $event): void
    {
        $this->revisionAdded = $event;
    }

    public function callback_revision1(): void
    {
        $this->revision1Event = true;
    }

    public function callback_revision2(): void
    {
        $this->revision2Event = true;
    }

    private function resetEvents(): void
    {
        $this->revision1Event = false;
        $this->revision2Event = false;
        $this->revisionAdded = null;
    }

    // endregion
}
