<?php

declare(strict_types=1);

namespace TestApplication\TestDriver\Revisionables;

use Application;
use Application\Revisionable\RevisionableException;
use Application_Changelog_FilterCriteria;
use Application_EventHandler_EventableListener;
use Application_RevisionableCollection;
use Application_RevisionableStateless;
use Application_Traits_Loggable;
use TestDriver\Revisionables\RevisionableMemoryCollection;

class RevisionableMemory extends Application_RevisionableStateless
{
    use Application_Traits_Loggable;

    public const EVENT_TEST_EVENT = 'TestEvent';

    /**
     * @var int
     */
    private int $revisionCounter = 0;

    public function getIdentification(): string
    {
        return 'Test revisionable (memory)';
    }

    public function getLabel(): string
    {
        return 'Test revisionable (memory)';
    }

    public function getID(): int
    {
        return 111;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     * @throws RevisionableException
     */
    public function setData(string $name, string $value): self
    {
        $this->revisions->setKey($name, $value);
        return $this;
    }

    public function getData(string $name): string
    {
        return (string)$this->revisions->getKey($name);
    }

    protected function _save(): void
    {
        // nothing to do here
    }

    /**
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onTriggerEvent(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_TEST_EVENT, $callback);
    }

    /**
     * @return $this
     */
    public function triggerTheEvent(): self
    {
        $this->triggerEvent(self::EVENT_TEST_EVENT);
        return $this;
    }

    public function createRevision(): int
    {
        $this->revisionCounter++;

        $user = Application::getUser();

        $this->revisions->addRevision(
            $this->revisionCounter,
            $user->getID(),
            $user->getName()
        );

        return $this->revisionCounter;
    }

    public function ignoreTestEvent(): void
    {
        $this->ignoreEvent(self::EVENT_TEST_EVENT);
    }

    public function ignoreRevisionAddedEvent(): void
    {
        $this->ignoreEvent(self::EVENT_REVISION_ADDED);
    }

    protected function _registerEvents(): void
    {

    }

    protected function _getChildDisposables(): array
    {
        return array();
    }

    protected function _disposeRevisionable(): void
    {

    }

    public function getLogIdentifier(): string
    {
        return $this->getIdentification();
    }

    protected function initStorageParts(): void
    {
    }

    public function getCustomKeyValues(): array
    {
        return array();
    }

    public function getChangelogTable(): string
    {
        return '';
    }

    public function configureChangelogFilters(Application_Changelog_FilterCriteria $filters): void
    {
    }

    public function getChangelogItemPrimary(): array
    {
        return array(
            'primary' => $this->getID()
        );
    }

    public function getChangelogItemInsertColumns(): array
    {
        return array(
            'primary' => $this->getID()
        );
    }

    public function getChangelogEntryText(string $type, array $data = array()): string
    {
        return '';
    }

    public function getChangelogEntryDiff(string $type, array $data = array()): ?array
    {
        return null;
    }

    public function getChangelogTypeLabel(string $type): string
    {
        return $type;
    }

    public function getCollection(): Application_RevisionableCollection
    {
        return RevisionableMemoryCollection::create();
    }

    protected function _getIdentification(): string
    {
        return 'MemoryRevisionable';
    }
}
