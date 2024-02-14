<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use Application_RevisionStorage_DB_CopyRevision;

class RevisionCopy extends Application_RevisionStorage_DB_CopyRevision
{
    protected function getParts() : array
    {
        return array();
    }
}
