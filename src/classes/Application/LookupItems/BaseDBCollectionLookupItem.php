<?php
/**
 * @package Application
 * @subpackage Lookup Items
 */

declare(strict_types=1);

namespace Application\LookupItems;

use DBHelper\BaseCollection\DBHelperCollectionInterface;

/**
 * Specialized item lookup class for use with DBHelper collections.
 * Removes some of the boilerplate code needed to implement a lookup item
 * for a collection.
 *
 * @package Application
 * @subpackage Lookup Items
 */
abstract class BaseDBCollectionLookupItem extends BaseLookupItem
{
    protected const string TABLE_ALIAS = 'main_tbl';

    abstract protected function getCollection() : DBHelperCollectionInterface;

    protected function idExists(int $id): bool
    {
        return $this->getCollection()->idExists($id);
    }

    protected function getByID(int $id): object
    {
        return $this->getCollection()->getByID($id);
    }

    protected function getPrimaryName(): string
    {
        return $this->getCollection()->getRecordPrimaryName();
    }

    protected function getSearchColumns(): array
    {
        $columns = $this->_getSearchColumns();
        $columns[] = self::TABLE_ALIAS.'.`'.$this->getPrimaryName().'`';
        return $columns;
    }

    /**
     * Fetches columns to search in if more can be searched
     * than the ID (added automatically).
     *
     * NOTE: Make sure to prepend names with {@see self::TABLE_ALIAS}
     * to reference the main table name.
     *
     * @return string[]
     */
    abstract protected function _getSearchColumns() : array;

    protected function getQuerySQL(): string
    {
        $collection = $this->getCollection();

        return "SELECT
    `".$collection->getRecordPrimaryName()."`
FROM
    `".$collection->getRecordTableName()."` AS ".self::TABLE_ALIAS."
WHERE
    {WHERE}";
    }
}
