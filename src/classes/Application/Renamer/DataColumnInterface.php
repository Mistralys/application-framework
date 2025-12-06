<?php

declare(strict_types=1);

namespace Application\Renamer;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface DataColumnInterface extends StringPrimaryRecordInterface
{
    public function getLabel() : string;
    public function getTableName() : string;
    public function getColumnName() : string;

    /**
     * @return string[]
     */
    public function getPrimaryColumns() : array;

    /**
     * @param string $searchTerm
     * @param bool $caseSensitive
     */
    public function indexEntries(string $searchTerm, bool $caseSensitive) : void;
}
