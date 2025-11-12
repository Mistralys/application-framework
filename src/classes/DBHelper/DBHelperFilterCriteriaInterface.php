<?php

declare(strict_types=1);

namespace DBHelper;

use Application\FilterCriteria\FilterCriteriaDBExtendedInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

interface DBHelperFilterCriteriaInterface extends FilterCriteriaDBExtendedInterface
{
    public function getCollection() : DBHelperCollectionInterface;
}
