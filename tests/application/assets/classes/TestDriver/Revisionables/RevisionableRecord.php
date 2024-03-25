<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use BaseDBCollectionStorage;
use TestDriver\Revisionables\ChangelogHandler;
use Application\Interfaces\ChangelogViaHandlerInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupTrait;
use Application\Traits\ChangelogViaHandlerTrait;
use Application_Revisionable;
use Application_RevisionableCollection;
use Application_RevisionableCollection_DBRevisionable;
use TestDriver\Revisionables\Storage\RevisionableStorage;

/**
 * @property RevisionableStorage $revisions
 */
class RevisionableRecord
    extends Application_RevisionableCollection_DBRevisionable
    implements
    StandardStateSetupInterface,
    ChangeLogViaHandlerInterface
{
    use StandardStateSetupTrait;
    use ChangelogViaHandlerTrait;

    public const DATA_KEY_NON_STRUCTURAL = 'non_structural_data_key';
    public const DATA_KEY_STRUCTURAL = 'structural_data_key';


    public function setAlias(string $alias) : self
    {
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

    public function getIdentification(): string
    {
        return sprintf('Revisionable [#%s]', $this->getID());
    }

    public function getChildDisposables(): array
    {
        return array();
    }

    protected function _dispose(): void
    {
    }

    public function getLogIdentifier(): string
    {
        return $this->getIdentification();
    }



    protected function _registerEvents(): void
    {
    }

    // endregion

    // region: B - Admin URLs

    public function getAdminStatusURL(array $params = array()): string
    {
    }

    public function getAdminChangelogURL(array $params = array()): string
    {
    }

    // endregion

    public function getChangelogHandlerClass(): string
    {
        return ChangelogHandler::class;
    }

    public static function createStubObject(): Application_Revisionable
    {
        return new self(RevisionableCollection::getInstance(), Application_RevisionableCollection::STUB_OBJECT_ID);
    }
}
