<?php

declare(strict_types=1);

namespace Application\Renamer;

use DBHelper;
use Application\Renamer\Index\RenamerIndex;
use DBHelper_Exception;

abstract class BaseDataColumn implements DataColumnInterface
{
    /**
     * Indexes entries matching the search term into the renamer index table.
     *
     * This implementation uses a single `INSERT ... SELECT ...` statement to
     * avoid loading data into PHP memory. It assumes that the database is modern
     * enough to support the `JSON_OBJECT` function.
     *
     * @param string $searchTerm
     * @param bool $caseSensitive
     * @return void
     *
     * @throws DBHelper_Exception
     */
    public function indexEntries(string $searchTerm, bool $caseSensitive) : void
    {
        // Build and execute the INSERT ... SELECT statement directly into the index table.
        $sql = sprintf(
            "INSERT INTO 
                `%s` (%s, %s, %s)
             SELECT
                 :column_id AS %s,
                 MD5(%s) AS %s,
                 JSON_OBJECT(%s) AS %s
             FROM 
                 `%s`
             WHERE 
                 %s",
            RenamerIndex::TABLE_NAME,
            RenamerIndex::COL_COLUMN_ID,
            RenamerIndex::COL_HASH,
            RenamerIndex::COL_PRIMARY_VALUES,
            RenamerIndex::COL_COLUMN_ID,
            $this->getColumnName(),
            RenamerIndex::COL_HASH,
            $this->buildJSONParts(),
            RenamerIndex::COL_PRIMARY_VALUES,
            $this->getTableName(),
            DBHelper::buildLIKEStatement(
                $this->getColumnName(),
                $searchTerm,
                $caseSensitive
            )
        );

        DBHelper::insert($sql, array('column_id' => $this->getID()));
    }

    /**
     * Build the arguments list for the JSON_OBJECT function using the
     * primary key columns.
     *
     * ## Example
     *
     * ```
     * JSON_OBJECT('id', id, 'site_id', site_id, ...)
     * ```
     *
     * @return string
     */
    private function buildJSONParts() : string
    {
        $arguments = array();
        foreach ($this->getPrimaryColumns() as $primary) {
            $arguments[] = sprintf("'%s', %s", $primary, $primary);
        }

        return implode(', ', $arguments);
    }
}
