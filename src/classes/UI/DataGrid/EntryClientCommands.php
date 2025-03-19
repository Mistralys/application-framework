<?php
/**
 * @package User Interface
 * @subpackage Data Grids
 */

declare(strict_types=1);

namespace UI\DataGrid;

use UI_DataGrid_Entry;

/**
 * Helper class used to generate clientside JavaScript
 * statements for functions of the data grid entry.
 *
 * # Usage
 *
 * Get an instance using the entry's {@see UI_DataGrid_Entry::clientCommands()}
 * method.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class EntryClientCommands
{
    private string $checkboxID;

    public function __construct(UI_DataGrid_Entry $entry)
    {
        $this->checkboxID = $entry->getCheckboxID();
    }

    /**
     * JS command to select the entry's checkbox.
     * @return string
     */
    public function select() : string
    {
        return sprintf(
            "%s.prop('checked', true);",
            $this->getCheckboxSelector()
        );
    }

    /**
     * JS command to deselect the entry's checkbox.
     * @return string
     */
    public function deselect() : string
    {
        return sprintf(
            "%s.prop('checked', true);",
            $this->getCheckboxSelector()
        );
    }

    /**
     * JS command to toggle the selected status of the entry's checkbox.
     * @return string
     */
    public function toggle() : string
    {
        $cmd = <<<'TPL'
%1$s.prop('checked', !%1$s.prop('checked'));
TPL;

        return sprintf($cmd, $this->getCheckboxSelector());
    }

    /**
     * Gets the jQuery selector for the entry's checkbox element.
     * @return string
     */
    public function getCheckboxSelector() : string
    {
        return sprintf(
            "$('#%s')",
            $this->checkboxID
        );
    }
}
