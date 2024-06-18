<?php
/**
 * @package Application Tests
 * @subpackage Stubs
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\RevisionDependentInterface;
use Application\Traits\RevisionDependentTrait;

/**
 * Implementation of a revision-dependent object.
 *
 * @package Application Tests
 * @subpackage Stubs
 *
 * @see RevisionDependentInterface
 * @see RevisionDependentTrait
 */
class RevisionDependentStub implements RevisionDependentInterface
{
    use RevisionDependentTrait;

    private RevisionableInterface $revisionable;
    private int $revision;

    public function __construct(RevisionableInterface $revisionable, int $revision)
    {
        $this->revisionable = $revisionable;
        $this->revision = $revision;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function getRevisionable(): RevisionableStatelessInterface
    {
        return $this->revisionable;
    }
}
