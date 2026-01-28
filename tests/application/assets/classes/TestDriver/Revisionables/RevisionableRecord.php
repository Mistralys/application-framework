<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application\Interfaces\ChangelogViaHandlerInterface;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupTrait;
use Application\Revisionable\Storage\BaseDBCollectionStorage;
use Application\Traits\ChangelogViaHandlerTrait;
use Application\EventHandler\Eventables\EventableListener;
use BaseRevisionable;

class RevisionableRecord
    extends BaseRevisionable
    implements
    StandardStateSetupInterface,
    ChangeLogViaHandlerInterface
{
    use StandardStateSetupTrait;
    use ChangelogViaHandlerTrait;

    public const string DATA_KEY_NON_STRUCTURAL = 'non_structural_data_key';
    public const string DATA_KEY_STRUCTURAL = 'structural_data_key';
    public const string EVENT_TEST_EVENT = 'TestEvent';

    public function setAlias(string $alias) : self
    {
        $this->getLogIdentifier();

        $this->setCustomKey(
            RevisionableCollection::COL_REV_ALIAS,
            $alias,
            true,
            ChangelogHandler::CHANGELOG_SET_ALIAS
        );

        return $this;
    }

    public function getRevisionStorage() : BaseDBCollectionStorage
    {
        return $this->revisions;
    }

    public function getStructuralKey() : string
    {
        return (string)$this->getRevisionKey(RevisionableCollection::COL_REV_STRUCTURAL);
    }

    public function getAlias() : string
    {
        return (string)$this->getRevisionKey(RevisionableCollection::COL_REV_ALIAS);
    }

    public function setStructuralKey(string $freeform) : self
    {
        $this->setCustomKey(
            RevisionableCollection::COL_REV_STRUCTURAL,
            $freeform,
            true
        );

        return $this;
    }

    public function setNonStructuralDataKey(string $value) : self
    {
        $this->setDataKey(
            self::DATA_KEY_NON_STRUCTURAL,
            $value,
            false
        );

        return $this;
    }

    public function getNonStructuralDataKey() : string
    {
        return (string)$this->getDataKey(self::DATA_KEY_NON_STRUCTURAL);
    }

    public function setStructuralDataKey(string $value) : self
    {
        $this->setDataKey(
            self::DATA_KEY_STRUCTURAL,
            $value,
            true
        );

        return $this;
    }

    public function getStructuralDataKey() : string
    {
        return (string)$this->getDataKey(self::DATA_KEY_STRUCTURAL);
    }

    // region: C - Saving data
    public function createTestRevision() : int
    {
        $this->startCurrentUserTransaction();

        $this->setStructuralDataKey('some_structural_key');

        $this->endTransaction();

        return $this->getRevision();
    }

    protected function initStorageParts(): void
    {
    }

    public function getCustomKeyValues() : array
    {
        return array(
            RevisionableCollection::COL_REV_STRUCTURAL => $this->getStructuralKey(),
            RevisionableCollection::COL_REV_ALIAS => $this->getAlias()
        );
    }

    // endregion

    // region: X - Interface methods

    protected function _getIdentification(): string
    {
        return sprintf(
            'Revisionable [#%s v%s]',
            $this->getID(),
            $this->getRevision()
        );
    }

    protected function _getIdentificationDisposed(): string
    {
        return sprintf(
            'Revisionable [#%s] (Disposed)',
            $this->getID()
        );
    }

    protected function _getChildDisposables(): array
    {
        return array();
    }

    protected function _disposeRevisionable(): void
    {
    }

    protected function _registerEvents(): void
    {
    }

    public function onTestEvent(callable $callback): EventableListener
    {
        return $this->addEventListener(self::EVENT_TEST_EVENT, $callback);
    }

    public function triggerTestEvent() : void
    {
        $this->triggerEvent(self::EVENT_TEST_EVENT);
    }

    public function ignoreTestEvent() : void
    {
        $this->ignoreEvent(self::EVENT_TEST_EVENT);
    }

    public function ignoreRevisionAddedEvent() : void
    {
        $this->ignoreEvent(self::EVENT_REVISION_ADDED);
    }

    // endregion

    // region: B - Admin URLs

    public function getAdminStatusURL(array $params = array()): string
    {
        return '';
    }

    public function getAdminChangelogURL(array $params = array()): string
    {
        return '';
    }

    // endregion

    public function getChangelogHandlerClass(): string
    {
        return ChangelogHandler::class;
    }

    public static function createStubObject(): RevisionableInterface
    {
        return new self(RevisionableCollection::getInstance(), RevisionableCollectionInterface::STUB_OBJECT_ID);
    }
}
