<?php

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

abstract class UI_DataGrid_Row_Sums_ColumnDef implements OptionableInterface
{
    use OptionableTrait;

    protected UI_DataGrid_Column $column;

    /**
     * @param UI_DataGrid_Column $column
     */
    public function __construct(UI_DataGrid_Column $column)
    {
        $this->column = $column;
    }

    public function getDataKey() : string
    {
        return $this->column->getDataKey();
    }

    abstract public function resolveContent() : string;
}
