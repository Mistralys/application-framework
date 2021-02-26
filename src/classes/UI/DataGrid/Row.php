<?php

declare(strict_types=1);

abstract class UI_DataGrid_Row
{
    /**
     * @var UI_DataGrid
     */
    protected $grid;

    public function __construct(UI_DataGrid $grid)
    {
        $this->grid = $grid;
    }

    abstract public function getEntry() : UI_DataGrid_Entry;
}
