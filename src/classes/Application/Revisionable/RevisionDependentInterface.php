<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Traits\RevisionDependentTrait;
use Application\Disposables\DisposableInterface;

/**
 * Interface for objects that are dependent on a revisionable's revision.
 *
 * NOTE: This includes the revisionable itself.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionDependentTrait
 */
interface RevisionDependentInterface
{
    public function getIdentification() : string;
    public function getRevision() : ?int;
    public function getRevisionable() : RevisionableStatelessInterface;

    /**
     * Verifies that the revision-dependent object is of the same class
     * as the subject.
     *
     * @param RevisionDependentInterface $dependent
     * @return self
     * @throws RevisionableException {@see self::ERROR_DEPENDENT_CLASS_MISMATCH}
     */
    public function requireRevisionableMatch(RevisionDependentInterface $dependent) : self;

    /**
     * Verifies that the revision-dependent object has the same
     * revision number as the subject.
     *
     * @param RevisionDependentInterface $dependent
     * @return $this
     * @throws RevisionableException {@see self::ERROR_DEPENDENT_REVISION_MISMATCH}
     */
    public function requireRevisionMatch(RevisionDependentInterface $dependent) : self;
}
