<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application_RevisionableCollection_DBRevisionable;
use Application_StateHandler_State;
use Closure;
use DBHelper;
use TestDriver\Revisionables\Storage\RevisionableStorage;

/**
 * @property RevisionableStorage $revisions
 */
class RevisionableRecord extends Application_RevisionableCollection_DBRevisionable
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_INACTIVE = 'inactive';
    public const STORAGE_SETTINGS = 'settings';

    public function makeFinalized() : self
    {
        $this->makeState($this->getStateByName(self::STATUS_FINALIZED));
        return $this;
    }

    public function makeInactive() : self
    {
        $this->makeState($this->getStateByName(self::STATUS_INACTIVE));
        return $this;
    }

    public function makeDeleted() : self
    {
        $this->makeState($this->getStateByName(self::STATUS_DELETED));
        return $this;
    }

    public function makeDraft() : self
    {
        $this->makeState($this->getStateByName(self::STATUS_DRAFT));
        return $this;
    }

    public function getLabel(): string
    {
        return (string)$this->revisions->getKey(RevisionableCollection::COL_REV_LABEL);
    }

    public function setLabel(string $label) : self
    {
        $this->setRevisionKey(
            RevisionableCollection::COL_REV_LABEL,
            $label,
            self::STORAGE_SETTINGS,
            false
        );

        return $this;
    }

    public function getStructuralKey() : string
    {
        return (string)$this->revisions->getKey(RevisionableCollection::COL_REV_STRUCTURAL);
    }

    public function setStructuralKey(string $freeform) : self
    {
        $this->setRevisionKey(
            RevisionableCollection::COL_REV_STRUCTURAL,
            $freeform,
            self::STORAGE_SETTINGS,
            true
        );

        return $this;
    }

    // region: C - Saving data

    protected function initStorageParts(): void
    {
        $this->registerStoragePart(self::STORAGE_SETTINGS, Closure::fromCallable(array($this, 'saveSettings')));
    }

    public function getCustomRevisionData() : array
    {
        return array(
            RevisionableCollection::COL_REV_LABEL => $this->getLabel(),
            RevisionableCollection::COL_REV_STRUCTURAL => $this->getStructuralKey()
        );
    }

    private function saveSettings() : void
    {
        $data = $this->getCustomRevisionData();
        $revKey = $this->collection->getRevisionKeyName();
        $data[$revKey] = $this->getRevision();

        DBHelper::updateDynamic(
            RevisionableCollection::TABLE_REVISIONS,
            $data,
            array($revKey)
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

    protected function buildStateDefs() : array
    {
        return array(
            self::STATUS_DRAFT => array(
                'label' => t('Draft'),
                'uiType' => Application_StateHandler_State::UI_TYPE_WARNING,
                'changesAllowed' => true,
                'initial' => true,
                'dependencies' => array(
                    self::STATUS_DRAFT,
                    self::STATUS_FINALIZED,
                    self::STATUS_DELETED,
                    self::STATUS_INACTIVE
                )
            ),
            self::STATUS_FINALIZED => array(
                'label' => t('Finalized'),
                'uiType' => Application_StateHandler_State::UI_TYPE_SUCCESS,
                'changesAllowed' => true,
                'onStructuralChange' => self::STATUS_DRAFT,
                'increasesPrettyRevision' => true,
                'dependencies' => array(
                    self::STATUS_DRAFT,
                    self::STATUS_DELETED,
                    self::STATUS_INACTIVE
                )
            ),
            self::STATUS_DELETED => array(
                'label' => t('Deleted'),
                'uiType' => Application_StateHandler_State::UI_TYPE_DANGER,
                'changesAllowed' => false,
                'dependencies' => array(
                    self::STATUS_DRAFT
                )
            ),
            self::STATUS_INACTIVE => array(
                'label' => t('Inactive'),
                'uiType' => Application_StateHandler_State::UI_TYPE_INACTIVE,
                'changesAllowed' => false,
                'dependencies' => array(
                    self::STATUS_DRAFT,
                    self::STATUS_DELETED
                )
            )
        );

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

}
