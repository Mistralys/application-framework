<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use Application_RevisionStorage_DB_CopyRevision;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionCopy extends Application_RevisionStorage_DB_CopyRevision
{
    protected function getParts() : array
    {
        return array(
            \Closure::fromCallable(array($this, 'saveSettings'))
        );
    }

    private function saveSettings() : void
    {
        $this->copyRecords(
            RevisionableCollection::TABLE_REVISIONS,
            RevisionableCollection::COL_REV_ID,
            array(RevisionableCollection::COL_REV_ID)
        );
    }
}
