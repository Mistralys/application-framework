<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\RequestTypes;

use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableInterface;

/**
 * @see RevisionableScreenInterface
 */
trait RevisionableScreenTrait
{
    private ?RevisionableInterface $resolvedRevisionable = null;
    private bool $revisionableResolved = false;

    public function getRevisionable() : ?RevisionableInterface
    {
        if($this->revisionableResolved) {
            return $this->resolvedRevisionable;
        }

        $this->revisionableResolved = true;
        $this->resolvedRevisionable = $this->_resolveRevisionable();

        return $this->resolvedRevisionable;
    }

    /**
     * Overridable method to customize how the revisionable is resolved.
     * @return RevisionableInterface|null
     */
    protected function _resolveRevisionable() : ?RevisionableInterface
    {
        return $this->createCollection()->getByRequest();
    }

    public function getRevisionableOrRedirect() : RevisionableInterface
    {
        $revisionable = $this->getRevisionable();
        if($revisionable !== null) {
            return $revisionable;
        }

        $this->redirectWithErrorMessage(
            t('No such record found.'),
            $this->createCollection()->getAdminListURL()
        );
    }
    public function requireRevisionable() : RevisionableInterface
    {
        $revisionable = $this->getRevisionable();
        if($revisionable !== null) {
            return $revisionable;
        }

        throw new RevisionableException(
            'No revisionable is available.',
            sprintf(
                'The screen [%s] expects a revisionable to be present.',
                get_class($this)
            ),
            RevisionableException::ERROR_REVISIONABLE_NOT_AVAILABLE
        );
    }

    protected function startSimulation(bool $outputToConsole=false) : bool
    {
        $this->requireRevisionable()->setSimulation();

        return parent::startSimulation($outputToConsole);
    }

    protected function endSimulation() : void
    {
        $this->requireRevisionable()->setSimulation(false);

        parent::endSimulation();
    }

    protected function startRevisionableTransaction(string $comments='') : void
    {
        parent::startTransaction();

        $this->requireRevisionable()->startCurrentUserTransaction($comments);
    }

    protected function endRevisionableTransaction() : void
    {
        $this->requireRevisionable()->endTransaction();

        parent::endTransaction();
    }
}
