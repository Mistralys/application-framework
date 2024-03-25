<?php
/**
 * @package User Interface
 * @package Data Grids
 */

declare(strict_types=1);

namespace UI\Interfaces;

use Application\Interfaces\FilterCriteriaInterface;
use Application_FilterCriteria;
use Application_FilterSettings;
use DateTime;
use UI_DataGrid;
use UI_Page_Sidebar;

/**
 * Interface for classes that build a list of records.
 *
 * @package User Interface
 * @package Data Grids
 * @see BaseListBuilder
 */
interface ListBuilderInterface
{
    public function getDataGrid(): UI_DataGrid;
    public function isColumnEnabled(string $colName): bool;
    public function disableColumn(string $colName): self;
    public function getFilterCriteria(): FilterCriteriaInterface;
    public function getFilterSettings(?array $settingValues = null): ?Application_FilterSettings;
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
