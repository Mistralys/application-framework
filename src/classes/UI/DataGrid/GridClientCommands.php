<?php
/**
 * @package User Interface
 * @subpackage Data Grids
 */

declare(strict_types=1);

namespace UI\DataGrid;

use UI_DataGrid;

/**
 * Helper class used to generate clientside JavaScript
 * statements for functions of the data grid.
 *
 * # Usage
 *
 * Get an instance using the grid's {@see UI_DataGrid::clientCommands()}
 * method.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class GridClientCommands
{
    private string $objectName;

    public function __construct(UI_DataGrid $grid)
    {
        $this->objectName = $grid->getClientObjectName();
    }

    /**
     * The name of the clientside variable referencing the grid object.
     * @return string
     */
    public function getObjectName() : string
    {
        return $this->objectName;
    }

    public function submitAction(string $actionName) : string
    {
        return sprintf(
            "%s.Submit('%s')",
            $this->objectName,
            $actionName
        );
    }

    public function toggleSelection() : string
    {
        return $this->getMethodCall('ToggleSelection');
    }

    public function toggleSelectAll() : string
    {
        return $this->getMethodCall('ToggleSelectAll');
    }

    public function selectAll() : string
    {
        return $this->getMethodCall('SelectAll');
    }

    public function deselectAll() : string
    {
        return $this->getMethodCall('DeselectAll');
    }

    private function getMethodCall(string $method) : string
    {
        return sprintf(
            '%s.%s()',
            $this->objectName,
            $method
        );
    }
}
