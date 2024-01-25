<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use Application_RevisionStorage_CollectionDB;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableStorage extends Application_RevisionStorage_CollectionDB
{
    public function getNextRevisionData(): array
    {
        return array(
            RevisionableCollection::COL_REV_LABEL => $this->revisionable->getLabel()
        );
    }
}
