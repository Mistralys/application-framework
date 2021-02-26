<?php

class UI_DataGrid_Action_Default extends UI_DataGrid_Action
{
    protected function init()
    {
        $this->attributes['onclick'] = sprintf(
        	"%s.Submit('%s')",
        	$this->grid->getClientObjectName(),
        	$this->name
        );
    }
}