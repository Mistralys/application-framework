<?php

declare(strict_types=1);

namespace Application\Traits;

use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\RevisionDependentInterface;

/**
 * @see RevisionDependentInterface
 */
trait RevisionDependentTrait
{
    /**
     * @inheritDoc
     * @return $this
     */
    public function requireRevisionableMatch(RevisionDependentInterface $dependent) : self
    {
        if(get_class($dependent->getRevisionable()) === get_class($this)) {
            return $this;
        }

        throw new RevisionableException(
            'Revisionable class mismatch',
            sprintf(
                'The revisionable class [%s] of the dependency does not match the current revisionable\'s class [%s].',
                get_class($dependent->getRevisionable()),
                get_class($this)
            ),
            RevisionableStatelessInterface::ERROR_DEPENDENT_CLASS_MISMATCH
        );
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function requireRevisionMatch(RevisionDependentInterface $dependent) : self
    {
        if($dependent->getRevision() === $this->getRevision()) {
            return $this;
        }

        throw new RevisionableException(
            'Revision mismatch',
            sprintf(
                'The revision [%s] of the dependency [%s] does not match the current revision [%s] in revisionable [%s].',
                $dependent->getRevision(),
                get_class($dependent),
                $this->getRevision(),
                $this->getIdentification()
            ),
            RevisionableStatelessInterface::ERROR_DEPENDENT_REVISION_MISMATCH
        );
    }
}
