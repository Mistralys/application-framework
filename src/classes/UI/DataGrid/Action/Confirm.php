<?php

class UI_DataGrid_Action_Confirm extends UI_DataGrid_Action_Default
{
    public function __construct(UI_DataGrid $grid, $name, $label, $confirmMessage)
    {
        parent::__construct($grid, $name, $label);

        $this->makeConfirm($confirmMessage);
    }
}