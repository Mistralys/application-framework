<?php

declare(strict_types=1);

namespace Application\FilterCriteria;

use Application_FilterCriteria_Database_ColumnUsage;
use Application_FilterCriteria_Database_CustomColumn;

interface FilterCriteriaDBExtendedInterface extends FilterCriteriaDBInterface
{
    /**
     * Fetches all select statements for custom columns.
     *
     * @return string[]
     */
    public function getCustomSelects() : array;

    /**
     * Check if the specified custom column has been added,
     * and is enabled. Returns false if it has been added,
     * but is not enabled.
     *
     * @param string $columnID
     * @return bool
     */
    public function hasCustomColumn(string $columnID) : bool;

    /**
     * Fetches a custom column instance.
     *
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     */
    public function getCustomColumn(string $columnID) : Application_FilterCriteria_Database_CustomColumn;

    /**
     * @param bool $debug
     * @return $this
     */
    public function debugBuild(bool $debug=true) : self;

    public function checkColumnUsage(Application_FilterCriteria_Database_CustomColumn $column) : Application_FilterCriteria_Database_ColumnUsage;

    public function handleColumnModified(Application_FilterCriteria_Database_CustomColumn $column) : void;

    /**
     * Detects all custom columns that are used in the
     * query, from the select statements to the order by
     * columns.
     *
     * @return Application_FilterCriteria_Database_CustomColumn[]
     */
    public function getActiveCustomColumns() : array;
}
