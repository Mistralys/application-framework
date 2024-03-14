<?php

declare(strict_types=1);

namespace Application\RevisionStorage\Copy;

use Application_RevisionableStateless;

class MemoryCopyRevision extends BaseRevisionCopy
{
    protected function getParts(): array
    {
        return array();
    }

    protected function _processDataKeys(Application_RevisionableStateless $targetRevisionable): void
    {
    }
}
