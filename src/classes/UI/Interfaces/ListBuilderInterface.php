<?php
/**
 * @package User Interface
 * @package Data Grids
 */

declare(strict_types=1);

namespace UI\Interfaces;

use Application\Interfaces\FilterCriteriaInterface;
use DateTime;
use FilterSettingsInterface;
use UI_DataGrid;
use UI_Page_Sidebar;
use UI_Renderable_Interface;

/**
 * Interface for classes that build a list of records.
 *
 * @package User Interface
 * @package Data Grids
 * @see BaseListBuilder
 */
interface ListBuilderInterface extends UI_Renderable_Interface
{
    public function getDataGrid(): UI_DataGrid;
    public function isColumnEnabled(string $colName): bool;
    public function disableColumn(string $colName): self;
    public function getFilterCriteria(): FilterCriteriaInterface;

    /**
     * @return \FilterSettingsInterface|NULL Can be `null` if there are no records to filter.
     */
    public function getFilterSettings(): ?FilterSettingsInterface;

    /**
     * Gets the filter criteria with all applied filters and settings.
     * @return FilterCriteriaInterface
     */
    public function getFilteredCriteria(): FilterCriteriaInterface;
    public function getFullViewTitle(): string;
    public function getEmptyMessage(): string;
    public function enableAdvancedMode(bool $enabled): self;
    public function addHiddenVars(array $vars): self;
    public function addHiddenVar(string $name, $value): self;
    public function disableEntryActions(): self;
    public function disableMultiActions(): self;
    public function setListID(string $id): self;
    public function handleActions(): self;
    public function addFilterSettings(UI_Page_Sidebar $sidebar): self;
    public function renderDate(?DateTime $date = null): string;
    public function adjustLabel(string $label): string;
    public function getRecordTypeLabelSingular(): string;
    public function getRecordTypeLabelPlural(): string;
    public function getPrimaryColumnName(): string;
    public function debug(bool $enabled = true): self;
}
