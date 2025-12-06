<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\RequestTypes;

use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableInterface;

/**
 * @see RevisionableScreenTrait
 */
interface RevisionableScreenInterface extends RevisionableCollectionScreenInterface
{
    public function createCollection() : RevisionableCollectionInterface;
    public function getRevisionable() : ?RevisionableInterface;
    public function getRevisionableOrRedirect() : RevisionableInterface;
    public function requireRevisionable() : RevisionableInterface;
}
