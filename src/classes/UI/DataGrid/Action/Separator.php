<?php

class UI_DataGrid_Action_Separator extends UI_DataGrid_Action
{
    public function __construct(UI_DataGrid $grid)
    {
        parent::__construct($grid, 'sep'.nextJSID(), '');
    }

    public function render()
    {
        if(!$this->isValid())
        {
            return '';
        }

        return '<li class="divider"></li>';
    }
}