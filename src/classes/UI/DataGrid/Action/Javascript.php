<?php

declare(strict_types=1);

class UI_DataGrid_Action_Javascript extends UI_DataGrid_Action
{
    /**
     * @var string
     */
    protected $statement;

    /**
     * @param UI_DataGrid $grid
     * @param string $name
     * @param string|number|UI_Renderable_Interface $label
     * @param string $statement
     * @throws UI_Exception
     */
    public function __construct(UI_DataGrid $grid, string $name, $label, string $statement)
    {
        parent::__construct($grid, $name, $label);

        $this->statement = $statement;
    }

    protected function init() : void
    {
        $this->attributes['onclick'] = sprintf(
        	$this->statement,
        	$this->grid->getClientObjectName(),
            "'".$this->name."'"
        );
    }
}
