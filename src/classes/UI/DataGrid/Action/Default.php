<?php

declare(strict_types=1);

class UI_DataGrid_Action_Default extends UI_DataGrid_Action
{
    protected function init() : void
    {
        $this->attributes['onclick'] = $this->grid->clientCommands()->submitAction($this->name, true);
    }
}
