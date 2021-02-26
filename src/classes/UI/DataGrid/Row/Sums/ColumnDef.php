<?php

declare(strict_types=1);

use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;

abstract class UI_DataGrid_Row_Sums_ColumnDef implements Interface_Optionable
{
    use Traits_Optionable;

    /**
     * @var UI_DataGrid_Column
     */
    protected $column;

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
