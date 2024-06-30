<?php
/**
 * @package Application
 * @subpackage FilterCriteria
 */

namespace Application\Interfaces;

use Application_Exception;
use Application_FilterCriteria;
use AppUtils\OperationResult;
use AppUtils\OperationResult_Collection;
use UI\PaginationRenderer;
use UI_DataGrid;

/**
 * Interface for classes that filter record collections:
 * Allows selecting criteria to filter the records with,
 * and fetch the matching records.
 *
 * This is meant to be extended. For database-related
 * filtering, use the specialized class {@see Application_FilterCriteria_Database}.
 *
 * @package Application
 * @subpackage FilterCriteria
 *
 * @see Application_FilterCriteria_Database
 * @see Application_FilterCriteria_DatabaseExtended
 */
interface FilterCriteriaInterface
{
    public const ORDER_DIR_DESCENDING = 'DESC';
    public const ORDER_DIR_ASCENDING = 'ASC';
    public const MESSAGE_TYPE_INFO = 'info';
    public const MESSAGE_TYPE_WARNING = 'warning';

    /**
     * @return $this
     */
    public function orderAscending() : self;

    /**
     * @return $this
     */
    public function orderDescending() : self;

    /**
     * @param string $orderDir
     * @return $this
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria::ERROR_INVALID_SORTING_ORDER
     */
    public function setOrderDir(string $orderDir) : self;

    /**
     * Sets the search terms string.
     *
     * @param string|NULL $search
     * @return $this
     */
    public function setSearch(?string $search): self;

    /**
     * @param PaginationRenderer $paginator
     * @return $this
     */
    public function setLimitByPagination(PaginationRenderer $paginator): self;

    /**
     * Sets the limit for the list.
     *
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function setLimit(int $limit = 0, int $offset = 0): self;

    /**
     * Sets the limit for the list by using an existing data
     * grid object, which contains the current pagination details.
     *
     * @param UI_DataGrid $datagrid
     * @return $this
     */
    public function setLimitFromDatagrid(UI_DataGrid $datagrid) : self;

    /**
     * Counts the amount of matching records,
     * according to the current filter criteria.
     *
     * NOTE: use the <code>countUnfiltered()</code>
     * method to count all records, without matching
     * the current criteria.
     *
     * @return int
     * @see Application_FilterCriteria::countUnfiltered()
     */
    public function countItems(): int;

    /**
     * Retrieves all matching items as an indexed array containing
     * associative array entries with the item data.
     *
     * @return array
     * @throws Application_Exception
     * @see getItem()
     */
    public function getItems() : array;

    /**
     * Counts the total, unfiltered number of entries.
     * @return integer
     */
    public function countUnfiltered(): int;

    /**
     * Sets the field to order the results by.
     *
     * @param string $fieldName
     * @param string $orderDir
     * @return $this
     *
     * @see Application_FilterCriteria::ERROR_INVALID_SORTING_ORDER
     */
    public function setOrderBy(string $fieldName, string $orderDir = self::ORDER_DIR_ASCENDING): self;

    public function getOrderField(): string;

    public function getOrderDir(): string;

    /**
     * @return string[]
     */
    public function getSearchTerms(): array;

    /**
     * Configure both the data grid and the filters using
     * the current settings, from the limit to the column
     * to order by and the order direction.
     *
     * @param UI_DataGrid $grid
     * @return $this
     * @throws Application_Exception
     */
    public function configure(UI_DataGrid $grid) : self;

    /**
     * Adds an information message.
     *
     * @param string $message
     * @return $this
     */
    public function addInfo(string $message) : self;

    /**
     * Adds a warning message.
     *
     * @param string $message
     * @return $this
     */
    public function addWarning(string $message) : self;

    /**
     * Adds an operation result instance as a message.
     *
     * @param OperationResult $message
     * @return self
     */
    public function addResultMessage(OperationResult $message) : self;

    public function hasMessages(): bool;

    public function getMessages(): OperationResult_Collection;

    /**
     * @return $this
     */
    public function resetMessages() : self;

    public function isCount(): bool;

    /**
     * Applies all filter criteria and determines the exact
     * composition needed for the filters.
     */
    public function applyFilters(): void;

    public function getInstanceID(): int;
}
