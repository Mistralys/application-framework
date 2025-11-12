<?php
/**
 * @package Application
 * @subpackage Lookup Items
 */

declare(strict_types=1);

namespace Application\LookupItems;

use Application\Revisionable\Collection\RevisionableCollectionInterface;

/**
 * Specialized item lookup class for use with revisionable collections.
 * Removes some of the boilerplate code needed to implement a lookup item
 * for a collection.
 *
 * The revisionable revision storage table is automatically joined, with the
 * current revision being selected. Make sure to use {@see self::REVS_ALIAS}
 * whenever you wish to reference this table.
 *
 * @package Application
 * @subpackage Lookup Items
 */
abstract class BaseRevisionableLookupItem extends BaseLookupItem
{
    protected const REVS_ALIAS = 'revs';

    abstract protected function getCollection() : RevisionableCollectionInterface;

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
        $names = $this->_getSearchColumns();
        $names[] = self::REVS_ALIAS.'.`'.$this->getPrimaryName().'`';
        return $names;
    }

    /**
     * Fetches columns to search in if more can be searched
     * than the ID (added automatically).
     *
     * NOTE: Make sure to prepend names with {@see self::REVS_ALIAS}
     * to search in revision table.
     *
     * @return string[]
     */
    abstract protected function _getSearchColumns() : array;

    protected function getQuerySQL(): string
    {
        $collection = $this->getCollection();

        return "SELECT
    ".self::REVS_ALIAS.".`".$collection->getRecordPrimaryName()."`
FROM
    `".$collection->getRevisionsTableName()."` AS ".self::REVS_ALIAS."
LEFT JOIN
    `".$collection->getCurrentRevisionsTableName()."` AS currev
ON
    ".self::REVS_ALIAS.".".$collection->getRecordPrimaryName()." = currev.".$collection->getRecordPrimaryName()."
WHERE
    ".self::REVS_ALIAS.".".$collection->getRevisionKeyName()." = currev.current_revision
AND
    {WHERE}
GROUP BY
    ".self::REVS_ALIAS.".`".$collection->getRecordPrimaryName()."`";
    }
}
