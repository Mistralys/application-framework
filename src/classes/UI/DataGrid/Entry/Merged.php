<?php

require_once ('UI/DataGrid/Entry.php');

class UI_DataGrid_Entry_Merged extends UI_DataGrid_Entry
{
    protected $title;
    
    public function __construct(UI_DataGrid $grid, $title)
    {
        parent::__construct($grid, array());
        $this->title = $title;
        $this->makeNonSortable();
    }
    
    public function render()
    {
        $this->addClass('row-merged');
        
        $trAttribs = array(
            'class' => implode(' ', $this->getClasses()),
        );
        
        $tdAttribs = array(
            'colspan' => $this->grid->countColumns()
        );
        
        $html =
        '<tr '.compileAttributes($trAttribs).'>' .
            '<td '.compileAttributes($tdAttribs).'>' .
                $this->title .
            '</td>' .
        '</tr>';
        
        return $html;
    }
}

