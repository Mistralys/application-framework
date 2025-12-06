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

    public function getItems() : array
    {
        $result = array();
        foreach($this->getItemsObjects() as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * @return MythologicalRecord[]
     */
    public function getItemsObjects(): array
    {
        return MythologyRecordCollection::getInstance()->getAll();
    }

    public function getIDKeyName(): string
    {
        return MythologicalRecord::KEY_ID;
    }
}
