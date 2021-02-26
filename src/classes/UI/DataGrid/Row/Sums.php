<?php

declare(strict_types=1);

class UI_DataGrid_Row_Sums extends UI_DataGrid_Row
{
    /**
     * @var array<string,UI_DataGrid_Row_Sums_ColumnDef>
     */
    protected $columns = array();

    /**
     * Defines a column sum to be generated via a callback function.
     *
     * @param string|UI_DataGrid_Column $colNameOrInstance
     * @param callable $callback
     * @param mixed[] $args Any additional arguments for the callback.
     * @return UI_DataGrid_Row_Sums
     */
    public function makeCallback($colNameOrInstance, $callback, array $args=array()) : UI_DataGrid_Row_Sums
    {
        $col = $this->resolveColumn($colNameOrInstance);

        array_unshift($args, $callback);

        $this->columns[$col->getDataKey()] = new UI_DataGrid_Row_Sums_ColumnDef_Callback($col, $callback, $args);

        return $this;
    }

    public function getEntry(): UI_DataGrid_Entry
    {
        $data = array();

        foreach($this->columns as $def)
        {
            $data[$def->getDataKey()] = $def->resolveContent();
        }

        return $this->grid->createEntry($data)
            ->addClass('sums-row')
            ->makeNonSortable();
    }

    /**
     * @param string|UI_DataGrid_Column $colNameOrInstance
     * @return UI_DataGrid_Column
     */
    protected function resolveColumn($colNameOrInstance) : UI_DataGrid_Column
    {
        if($colNameOrInstance instanceof UI_DataGrid_Column)
        {
            return $colNameOrInstance;
        }

        return $this->grid->getColumnByName($colNameOrInstance);
    }
}
