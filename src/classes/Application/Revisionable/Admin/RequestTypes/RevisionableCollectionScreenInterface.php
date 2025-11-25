<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\RequestTypes;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Revisionable\Collection\RevisionableCollectionInterface;

interface RevisionableCollectionScreenInterface extends AdminScreenInterface
{
    public function createCollection() : RevisionableCollectionInterface;
}
