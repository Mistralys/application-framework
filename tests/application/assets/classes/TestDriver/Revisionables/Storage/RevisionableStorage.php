<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use Application_RevisionStorage_CollectionDB;
use TestDriver\Revisionables\RevisionableRecord;

/**
 * @see RevisionCopy
 *
 * @property RevisionableRecord $revisionable
 */
class RevisionableStorage extends Application_RevisionStorage_CollectionDB
{
    public function getNextRevisionData(): array
    {
        return $this->revisionable->getCustomRevisionData();
    }
}
