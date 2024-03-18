<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable;

/**
 * Interface for objects that are dependent on a revisionable's revision.
 *
 * NOTE: This includes the revisionable itself.
 *
 * @package Application
 * @subpackage Revisionables
 */
interface RevisionDependentInterface
{
    public function getRevision() : ?int;
    public function getRevisionable() : RevisionableStatelessInterface;
}
