<?php

class UI_DataGrid_Column_MultiSelect extends UI_DataGrid_Column
{
    const ERROR_COLUMN_CANNOT_BE_EDITABLE = 513131;
    
    public function __construct(UI_DataGrid $grid)
    {
        parent::__construct($grid, 0, null, null);
        $this->roleActions();
    }
    
    public function getType()
    {
        return 'MultiSelect';
    }

    public function renderCell(UI_DataGrid_Entry $entry)
    {
        if($entry->hasClass('sums-row'))
        {
            return '<td class="role-actions"></td>';
        }

        $primary = $entry->getPrimaryValue();
        
        if($primary === null || $primary === '') {
            throw new Application_Exception(
                'Invalid DataGrid configuration',
                'The primary key set for the multiselect column does not exist in the data set for the data grid "' . $this->grid->getID() . '".'
            );
        }
        
        $selected = '';
        if($entry->isSelected()) {
            $selected = ' checked="checked"';
        }

        $html =
        '<td class="role-actions">' .
            '<input id="'.$entry->getCheckboxID().'" type="checkbox" name="datagrid_items[]" value="' . $primary . '"'.$selected.' onchange="'.$this->grid->getClientObjectName().'.Handle_SelectionChanged($(this))"/>' .
        '</td>';

        return $html;
    }

    public function renderHeaderCell()
    {
        $html =
        '<th style="width:1%" class="role-actions">' .
        	'<input type="checkbox" onchange="'.$this->grid->getClientObjectName().'.ToggleSelection();$(this).attr(\'checked\', false)"/>' .
        '</th>';

        return $html;
    }

    public function setEditable($clientObjectName)
    {
        throw new Application_Exception(
            'Column cannot be editable',
            'The MultiSelect column cannot be set as editable.',
            self::ERROR_COLUMN_CANNOT_BE_EDITABLE        
        );   
    }
    
    public function isAction()
    {
        return true;
    }
}