<?php

declare(strict_types=1);

class TestRevisionableMemory extends Application_RevisionableStateless
{
    use Application_Traits_Loggable;

    public const EVENT_TEST_EVENT = 'TestEvent';

    /**
     * @var int
     */
    private int $revisionCounter = 0;

    public function getIdentification() : string
    {
        return 'Test revisionable (memory)';
    }

    public function getLabel() : string
    {
        return 'Test revisionable (memory)';
    }

    public function getID() : int
    {
        return 111;
    }

    public function setData(string $name, string $value) : TestRevisionableMemory
    {
        $this->revisions->setKey($name, $value);
        return $this;
    }

    public function getData(string $name) : string
    {
        return (string)$this->revisions->getKey($name);
    }

    protected function _save() : void
    {
        // nothing to do here
    }

    public function onTriggerEvent(callable $callback) : TestRevisionableMemory
    {
        $this->addEventHandler(self::EVENT_TEST_EVENT, $callback);
        return $this;
    }

    public function triggerTheEvent() : TestRevisionableMemory
    {
        $this->triggerEvent(self::EVENT_TEST_EVENT);
        return $this;
    }

    public function createRevision() : int
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

    public function ignoreTestEvent() : void
    {
        $this->ignoreEvent(self::EVENT_TEST_EVENT);
    }

    public function ignoreRevisionAddedEvent() : void
    {
        $this->ignoreEvent(self::EVENT_REVISION_ADDED);
    }

    protected function _registerEvents() : void
    {

    }

    public function getChildDisposables() : array
    {
        return array();
    }

    protected function _dispose() : void
    {

    }

    public function getLogIdentifier() : string
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
        return array('primary');
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
}
