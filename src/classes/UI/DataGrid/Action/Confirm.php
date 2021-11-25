<?php

declare(strict_types=1);

class UI_DataGrid_Action_Confirm extends UI_DataGrid_Action_Default
{
    /**
     * @param UI_DataGrid $grid
     * @param string $name
     * @param string|number|UI_Renderable_Interface $label
     * @param string|number|UI_Renderable_Interface $confirmMessage
     * @throws UI_Exception
     */
    public function __construct(UI_DataGrid $grid, string $name, $label, $confirmMessage)
    {
        parent::__construct($grid, $name, $label);

        $this->makeConfirm($confirmMessage);
    }
}
