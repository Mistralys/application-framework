<?php

declare(strict_types=1);

namespace DBHelper;

use Application\FilterCriteria\FilterCriteriaDBExtendedInterface;
use DBHelper\BaseFilterCriteria\IntegerCollectionFilteringInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;

interface DBHelperFilterCriteriaInterface extends FilterCriteriaDBExtendedInterface
{
    /**
     * @return DBHelperRecordInterface[]
     */
    public function getItemsObjects(): array;

    /**
     * @return DBHelper_BaseFilterCriteria_Record[]
     */
    public function getItemsDetailed() : array;
}
