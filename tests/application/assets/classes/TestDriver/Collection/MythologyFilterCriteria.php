<?php

declare(strict_types=1);

namespace TestDriver\Collection;

use Application_FilterCriteria;

class MythologyFilterCriteria extends Application_FilterCriteria
{
    public function countItems(): int
    {
        return count($this->getItems());
    }

    /**
     * @return MythologicalRecord[]
     */
    public function getItems(): array
    {
        return MythologyRecordCollection::getInstance()->getAll();
    }
}
