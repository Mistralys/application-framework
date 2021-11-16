<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

class UI_DataGrid_Column_MultiSelect extends UI_DataGrid_Column
{
    public const ERROR_COLUMN_CANNOT_BE_EDITABLE = 513131;
    
    public function __construct(UI_DataGrid $grid)
    {
        parent::__construct($grid, 0, '', '');

        $this->roleActions();
    }
    
    public function getType() : string
    {
        return 'MultiSelect';
    }

    public function renderCell(UI_DataGrid_Entry $entry) : string
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
        
        $objectName = $this->grid->getClientObjectName();

        OutputBuffering::start();

        ?>
        <td class="role-actions">
            <input
                id="<?php echo $entry->getCheckboxID() ?>"
                type="checkbox"
                name="datagrid_items[]"
                value="<?php echo $primary ?>"
                onchange="<?php echo $objectName ?>.Handle_SelectionChanged($(this))"
                <?php
                if($entry->isSelected()) {
                    echo ' checked="checked"';
                }
                ?>
            />
        </td>
        <?php

        return OutputBuffering::get();
    }

    public function renderHeaderCell(bool $duplicate=false) : string
    {
        OutputBuffering::start();

        $objectName = $this->grid->getClientObjectName();

        ?>
        <th style="width:1%" class="role-actions">
        	<input
                type="checkbox"
                onchange="<?php echo $objectName ?>.ToggleSelection();$(this).attr('checked', false)"
            />
        </th>
        <?php

        return OutputBuffering::get();
    }

    /**
     * @param string $clientClassName
     * @return UI_DataGrid_Column
     * @throws UI_DataGrid_Exception
     */
    public function setEditable(string $clientClassName) : UI_DataGrid_Column
    {
        throw new UI_DataGrid_Exception(
            'Column cannot be editable',
            'The MultiSelect column cannot be set as editable.',
            self::ERROR_COLUMN_CANNOT_BE_EDITABLE        
        );   
    }
    
    public function isAction() : bool
    {
        return true;
    }
}
