<?php

declare(strict_types=1);

namespace Application\Renamer;

use Application\Renamer\Index\RenamerIndex;
use DBHelper;
use DBHelper_Exception;

abstract class BaseDataColumn implements DataColumnInterface
{
    /**
     * Implementing classes can provide additional WHERE conditions for the
     * index SELECT statement. Each entry should be a full SQL condition
     * fragment without the "WHERE" keyword (e.g. "status = 'active'").
     *
     * All returned conditions will be AND-ed together with the LIKE
     * statement for the column search.
     *
     * @return array<int,string>
     */
    protected function _getWhereStatements() : array
    {
        return array();
    }

    /**
     * Indexes entries matching the search term into the renamer index table.
     *
     * This implementation uses a single `INSERT ... SELECT ...` statement to
     * avoid loading data into PHP memory. It assumes that the database is modern
     * enough to support the `JSON_OBJECT` function.
     *
     * @param string $searchTerm
     * @param bool $caseSensitive
     * @param string|null $extraWhere Optional additional WHERE condition to AND with the LIKE statement.
     * @return void
     *
     * @throws DBHelper_Exception
     */
    final public function indexEntries(string $searchTerm, bool $caseSensitive, ?string $extraWhere = null) : void
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
            $this->buildWhereStatements($searchTerm, $caseSensitive)
        );

        DBHelper::insert($sql, array('column_id' => $this->getID()));
    }

    private function getWhereStatements(string $searchTerm, bool $caseSensitive) : array
    {
        $statements = array(DBHelper::buildLIKEStatement(
            $this->getColumnName(),
            $searchTerm,
            $caseSensitive
        ));

        array_push($statements, ...$this->_getWhereStatements());

        return $statements;
    }

    private function buildWhereStatements(string $searchTerm, bool $caseSensitive) : string
    {
        // Join all conditions with AND, wrapping each in parentheses
        $wrapped = array_map(
            static function (string $part) : string {
                return '(' . $part . ')';
            },
            $this->getWhereStatements($searchTerm, $caseSensitive)
        );

        return implode(' AND ', $wrapped);
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
