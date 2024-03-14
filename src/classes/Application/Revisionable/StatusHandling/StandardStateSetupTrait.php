<?php

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

use Application_StateHandler_State;

trait StandardStateSetupTrait
{
    protected function buildStateDefs() : array
    {
        return array(
            StandardStateSetupInterface::STATUS_DRAFT => array(
                'label' => t('Draft'),
                'uiType' => Application_StateHandler_State::UI_TYPE_WARNING,
                'changesAllowed' => true,
                'initial' => true,
                'dependencies' => array(
                    StandardStateSetupInterface::STATUS_DRAFT,
                    StandardStateSetupInterface::STATUS_FINALIZED,
                    StandardStateSetupInterface::STATUS_DELETED,
                    StandardStateSetupInterface::STATUS_INACTIVE
                )
            ),
            StandardStateSetupInterface::STATUS_FINALIZED => array(
                'label' => t('Finalized'),
                'uiType' => Application_StateHandler_State::UI_TYPE_SUCCESS,
                'changesAllowed' => true,
                'onStructuralChange' => StandardStateSetupInterface::STATUS_DRAFT,
                'increasesPrettyRevision' => true,
                'dependencies' => array(
                    StandardStateSetupInterface::STATUS_DRAFT,
                    StandardStateSetupInterface::STATUS_DELETED,
                    StandardStateSetupInterface::STATUS_INACTIVE
                )
            ),
            StandardStateSetupInterface::STATUS_DELETED => array(
                'label' => t('Deleted'),
                'uiType' => Application_StateHandler_State::UI_TYPE_DANGER,
                'changesAllowed' => false,
                'dependencies' => array(
                    StandardStateSetupInterface::STATUS_DRAFT
                )
            ),
            StandardStateSetupInterface::STATUS_INACTIVE => array(
                'label' => t('Inactive'),
                'uiType' => Application_StateHandler_State::UI_TYPE_INACTIVE,
                'changesAllowed' => false,
                'dependencies' => array(
                    StandardStateSetupInterface::STATUS_DRAFT,
                    StandardStateSetupInterface::STATUS_DELETED
                )
            )
        );
    }

    public function makeFinalized() : self
    {
        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_FINALIZED));
        return $this;
    }

    public function makeInactive() : self
    {
        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_INACTIVE));
        return $this;
    }

    public function makeDeleted() : self
    {
        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_DELETED));
        return $this;
    }

    public function makeDraft() : self
    {
        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_DRAFT));
        return $this;
    }

    public function isFinalized() : bool
    {
        return $this->isState(StandardStateSetupInterface::STATUS_FINALIZED);
    }

    public function isInactive() : bool
    {
        return $this->isState(StandardStateSetupInterface::STATUS_INACTIVE);
    }

    public function isDeleted() : bool
    {
        return $this->isState(StandardStateSetupInterface::STATUS_DELETED);
    }

    public function isDraft() : bool
    {
        return $this->isState(StandardStateSetupInterface::STATUS_DRAFT);
    }
}
