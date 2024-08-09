<?php

class UI_DataGrid_Entry_Heading extends UI_DataGrid_Entry
{
    protected $title;
    
    public function __construct(UI_DataGrid $grid, $title)
    {
        parent::__construct($grid, array());
        $this->title = $title;
        $this->makeNonSortable();
    }

    public function isCountable() : bool
    {
        return false;
    }

    public function render() : string
    {
        $this->addClass('row-heading');
        
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

