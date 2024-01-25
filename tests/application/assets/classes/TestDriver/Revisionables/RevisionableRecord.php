<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application_RevisionableCollection_DBRevisionable;
use Application_StateHandler_State;

class RevisionableRecord extends Application_RevisionableCollection_DBRevisionable
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_INACTIVE = 'inactive';

    public function makeFinalized() : self
    {
        return $this->setState($this->getStateByName(self::STATUS_FINALIZED));
    }

    public function makeInactive() : self
    {
        return $this->setState($this->getStateByName(self::STATUS_INACTIVE));
    }

    public function makeDeleted() : self
    {
        return $this->setState($this->getStateByName(self::STATUS_DELETED));
    }

    public function makeDraft() : self
    {
        return $this->setState($this->getStateByName(self::STATUS_DRAFT));
    }

    public function getLabel(): string
    {
        return (string)$this->revisions->getKey(RevisionableCollection::COL_REV_LABEL);
    }

    public function setLabel(string $label) : self
    {
        $this->revisions->setKey(RevisionableCollection::COL_REV_LABEL, $label);
        return $this;
    }

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

    protected function _saveWithStateChange() : void
    {
    }

    protected function _save() : void
    {
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
