<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use Application\Revisionable\Storage\Copy\BaseDBRevisionCopy;

class RevisionCopy extends BaseDBRevisionCopy
{
    protected function getParts() : array
    {
        return array();
    }
}
