<?php

declare(strict_types=1);

namespace DBHelper\BaseFilterCriteria;

interface StringCollectionFilteringInterface extends BaseCollectionFilteringInterface
{
    public function idExists(string $record_id): bool;
}
