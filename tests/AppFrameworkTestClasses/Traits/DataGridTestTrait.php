<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use UI_DataGrid;

trait DataGridTestTrait
{
    protected function createDataGrid(?string $id=null) : UI_DataGrid
    {
        if(empty($id)) {
            $id = 'grid'.$this->getTestCounter('dataGrid');
        }

        return $this->createUI()->createDataGrid($id);
    }
}
