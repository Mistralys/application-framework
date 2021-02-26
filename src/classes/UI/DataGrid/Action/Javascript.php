<?php

class UI_DataGrid_Action_Javascript extends UI_DataGrid_Action
{
    protected $statement;

    public function __construct(UI_DataGrid $grid, $name, $label, $statement)
    {
        parent::__construct($grid, $name, $label);
        $this->statement = $statement;
    }

    protected function init()
    {
        $this->attributes['onclick'] = sprintf(
        	$this->statement,
        	$this->grid->getClientObjectName(),
            "'".$this->name."'"
        );
    }
}