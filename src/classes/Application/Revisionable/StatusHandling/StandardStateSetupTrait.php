<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

use Application\Revisionable\RevisionableException;
use Application\StateHandler\StateHandlerException;
use Application_StateHandler_State;

/**
 * Trait used to implement the standard state setup
 * for revisionables, using four states:
 *
 * 1. Draft - Being edited / not published yet.
 * 2. Finalized - Published or ready to publish.
 * 3. Inactive - Not in use, but not deleted, can be recovered as a draft.
 * 4. Deleted - Deleted, can be destroyed at the earliest convenience.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see StandardStateSetupInterface
 */
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

    /**
     * @inheritDoc
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeFinalized(?string $comments=null) : self
    {
        if (empty($comments)) {
            $comments = t('Finalized the %1$s.', $this->getCollection()->getRecordReadableNameSingular());
        }

        $this->makeState($this->stateHandler->getStateByName(StandardStateSetupInterface::STATUS_FINALIZED), $comments);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeInactive(?string $comments=null) : self
    {
        if (empty($comments)) {
            $comments = t('Marked the %1$s as inactive.', $this->getCollection()->getRecordReadableNameSingular());
        }

        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_INACTIVE), $comments);
        return $this;
    }

    /**
     * @inheritDoc
     * @param string|null $comments
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeDeleted(?string $comments=null) : self
    {
        if (empty($comments)) {
            $comments = t('Marked the %1$s as deleted.', $this->getCollection()->getRecordReadableNameSingular());
        }

        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_DELETED), $comments);
        return $this;
    }

    /**
     * @inheritDoc
     * @param string|null $comments
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeDraft(?string $comments=null) : self
    {
        if (empty($comments)) {
            $comments = t('Turned the %1$s into a draft.', $this->getCollection()->getRecordReadableNameSingular());
        }

        $this->makeState($this->getStateByName(StandardStateSetupInterface::STATUS_DRAFT), $comments);
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

    public function canBeMadeInactive() : bool
    {
        return $this
            ->requireState()
            ->hasDependency($this->getStateByName(StandardStateSetupInterface::STATUS_INACTIVE));
    }

    public function canBeFinalized() : bool
    {
        return $this
            ->requireState()
            ->hasDependency($this->getStateByName(StandardStateSetupInterface::STATUS_FINALIZED));
    }

    public function canBeDeleted() : bool
    {
        return true;
    }

    public function canBeDestroyed() : bool
    {
        return $this->isDeleted();
    }
}
