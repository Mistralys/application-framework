<?php

declare(strict_types=1);

namespace Application\Renamer;

use DBHelper;
use DBHelper_FetchMany;
use Application\Renamer\Index\RenamerRecord;

abstract class BaseDataColumn implements DataColumnInterface
{
    public function indexEntries(string $searchTerm, bool $caseSensitive) : void
    {
        foreach($this->fetchData($searchTerm, $caseSensitive) as $data) {
            RenamerRecord::insert($this, $data);
        }
    }

    private function fetchData(string $searchTerm, bool $caseSensitive) : array
    {
        return DBHelper::fetchAll(sprintf(
            "
            SELECT 
                %s 
            FROM
                %s
            WHERE 
                %s",
            implode(", ", $this->getSelectColumns()),
            $this->getTableName(),
            DBHelper::buildLIKEStatement(
                $this->getColumnName(),
                $searchTerm,
                $caseSensitive
            )
        ));
    }

    private function getSelectColumns() : array
    {
        return array_merge(
            $this->getPrimaryColumns(),
            array($this->getColumnName())
        );
    }
}
