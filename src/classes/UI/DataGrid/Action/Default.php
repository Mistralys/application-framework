<?php

declare(strict_types=1);

class UI_DataGrid_Action_Default extends UI_DataGrid_Action
{
    protected function init() : void
    {
        $this->attributes['onclick'] = sprintf(
        	"%s.Submit('%s')",
        	$this->grid->getClientObjectName(),
        	$this->name
        );
    }
}
