<?php

declare(strict_types=1);

namespace Application\Tags;

use Application_FilterCriteria;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use function PhpParser\defineCompatibilityTokens;

class TagSortType implements StringPrimaryRecordInterface
{
    private string $id;
    private string $label;
    private bool $ascending;
    private ?string $column;

    public function __construct(string $id, ?string $column, string $label, bool $ascending = true)
    {
        $this->id = $id;
        $this->label = $label;
        $this->column = $column;
        $this->ascending = $ascending;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getSortColumn() : ?string
    {
        return $this->column;
    }

    public function getSortDirection() : string
    {
        if($this->isAscending()) {
            return Application_FilterCriteria::ORDER_DIR_ASCENDING;
        }

        return Application_FilterCriteria::ORDER_DIR_DESCENDING;
    }

    public function isAscending() : bool
    {
        return $this->ascending;
    }

    public function isDescending() : bool
    {
        return !$this->isAscending();
    }

    public function isInherited() : bool
    {
        return $this->getID() === TagSortTypes::SORT_INHERIT;
    }
}
