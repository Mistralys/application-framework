<?php
/**
 * @package Maileditor
 * @subpackage Mails
 */

declare(strict_types=1);

use Application\Driver\DriverException;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Microtime;
use AppUtils\Traits\OptionableTrait;

/**
 * Helper class that is used to create a list of records
 * in an administration screen, with options to customize it.
 * It handles the collecting of data and the rendering.
 *
 * @package User Interface
 * @subpackage DataGrids
 */
abstract class BaseListBuilder implements OptionableInterface
{
    use OptionableTrait;

    public const ERROR_LIST_ALREADY_INITIALIZED = 86001;

    // region Z - Abstract methods

    abstract protected function createFilterCriteria() : Application_FilterCriteria;
    abstract protected function configureFilters() : void;
    abstract public function getFullViewTitle() : string;
    abstract public function getEmptyMessage() : string;
    abstract public function getRecordTypeLabelSingular() : string;
    abstract public function getRecordTypeLabelPlural() : string;
    abstract protected function configureColumns(UI_DataGrid $grid) : void;
    abstract public function getPrimaryColumnName() : string;
    abstract protected function configureActions(UI_DataGrid $grid) : void;
    abstract protected function resolveRecord(array $itemData) : object;
    abstract protected function createFilterSettings() : Application_FilterSettings;
    abstract protected function preRender() : void;

    /**
     * @param object $record
     * @return array<string,mixed>
     */
    abstract protected function collectEntry(object $record) : array;
    
    // endregion

    // region A - Utility methods

    public function getDataGrid() : UI_DataGrid
    {
        if (isset($this->dataGrid))
        {
            return $this->dataGrid;
        }

        $grid = $this->ui->createDataGrid($this->listID);

        $this->dataGrid = $grid;

        $grid->setFullViewTitle($this->getFullViewTitle());

        $grid->setEmptyMessage($this->getEmptyMessage());

        if (isset($this->filterSettings) && $this->filterSettings->isActive())
        {
            $grid->setEmptyMessage(t('No %1$s found matching the selected filter criteria.', $this->getRecordTypeLabelPlural()));
        }

        $this->configureColumns($grid);
        $grid->enableColumnControls(6);
        $grid->enableLimitOptions(UI_DataGrid::DEFAULT_LIMIT_CHOICES);
        $this->configureDataGridActions();

        return $grid;
    }

    public function isColumnEnabled(string $colName) : bool
    {
        return !in_array($colName, $this->hiddenColumns, true);
    }

    public function disableColumn(string $colName) : BaseListBuilder
    {
        if(!in_array($colName, $this->hiddenColumns, true)) {
            $this->hiddenColumns[] = $colName;
        }

        return $this;
    }

    /**
     * @return Application_FilterCriteria
     * @throws Application_Exception
     */
    public function getFilterCriteria() : Application_FilterCriteria
    {
        if(isset($this->filters)) {
            return $this->filters;
        }

        $this->filters = $this->createFilterCriteria();

        $this->configureFilters();

        return $this->filters;
    }

    /**
     * Can be null if there are no records to filter.
     *
     * @param array<string,mixed>|null $settingValues
     * @return Application_FilterSettings|NULL
     * @throws Application_Exception
     * @throws UI_Exception
     * @throws DriverException
     */
    public function getFilterSettings(?array $settingValues=null) : ?Application_FilterSettings
    {
        if(!$this->hasRecords) {
            return null;
        }

        if(!isset($this->filterSettings)) {
            $this->filterSettings = $this->createFilterSettings()
                ->setID($this->listID)
                ->setSettings($settingValues);
        }

        return $this->filterSettings;
    }

    public function getFilteredCriteria() : Application_FilterCriteria
    {
        $filters = $this->getFilterCriteria();
        $settings = $this->getFilterSettings();

        if($settings)
        {
            $settings->configureFilters($filters);
        }

        return $filters;
    }

    public function debug(bool $enabled=true) : BaseListBuilder
    {
        $this->debug = $enabled;

        if(isset($this->filters))
        {
            $this->filters->debugQuery($enabled);
        }

        return $this;
    }

    public function enableAdvancedMode(bool $enabled) : self
    {
        $this->advancedMode = $enabled;
        return $this;
    }

    /**
     * @param array<string,string> $vars
     * @return $this
     */
    public function addHiddenVars(array $vars) : BaseListBuilder
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string|int|float $value
     * @return $this
     */
    public function addHiddenVar(string $name, $value) : BaseListBuilder
    {
        $this->hiddenVars[$name] = (string)$value;
        return $this;
    }

    public function disableEntryActions() : BaseListBuilder
    {
        $this->setOption('disable-entry-actions', true);
        return $this;
    }

    public function disableMultiActions() : BaseListBuilder
    {
        $this->setOption('disable-multi-actions', true);
        return $this;
    }

    // endregion

    // region W - Internal handling

    protected UI $ui;
    protected Application_User $user;
    protected ?Application_FilterSettings $filterSettings = null;
    protected bool $hasRecords;
    protected ?UI_DataGrid $dataGrid = null;
    protected string $listID;
    protected ?Application_FilterCriteria $filters = null;
    protected Application_Admin_ScreenInterface $screen;
    protected bool $debug = false;
    protected bool $advancedMode = false;

    /**
     * @var string[]
     */
    protected array $hiddenColumns = array();

    /**
     * @var array<string,string>
     */
    protected array $hiddenVars = array();


    public function __construct(Application_Admin_ScreenInterface $screen, string $listID='')
    {
        $this->screen = $screen;
        $this->ui = $screen->getRenderer()->getUI();
        $this->user = Application_Driver::getInstance()->getUser();
        $this->hasRecords = $this->getFilterCriteria()->countUnfiltered() > 0;
        $this->listID = $listID;
    }

    public function getDefaultOptions(): array
    {
        return array(
            'disable-multi-actions' => false,
            'disable-entry-actions' => false
        );
    }

    public function render() : string
    {
        $grid = $this->getDataGrid();

        $this->preRender();

        return $grid->render($this->collectEntries());
    }

    protected function configureDataGridActions() : void
    {
        if($this->getBoolOption('disable-multi-actions')) {
            return;
        }

        $grid = $this->getDataGrid();

        $grid->enableMultiSelect($this->getPrimaryColumnName());

        $this->configureActions($grid);
    }

    protected function collectEntries() : array
    {
        $settings = $this->getFilterSettings();

        if ($settings === null) {
            return array();
        }

        $settings->addHiddenVars($this->hiddenVars);

        $filters = $this->getFilterCriteria();
        $grid = $this->getDataGrid();

        $grid->configure($settings, $filters);
        $grid->addHiddenVars($this->hiddenVars);
        $grid->addHiddenVars($this->screen->getPageParams());

        $items = $filters->getItems();
        $entries = array();

        foreach ($items as $item)
        {
            $entries[] = $this->collectEntry($this->resolveRecord($item));
        }

        return $entries;
    }

    /**
     * Sets the ID to use for the list and the filter settings.
     * Lists that use the same ID share the same filter settings.
     *
     * @param string $id
     * @return BaseListBuilder
     * @throws UI_DataGrid_Exception
     * @see BaseListBuilder::ERROR_LIST_ALREADY_INITIALIZED
     */
    public function setListID(string $id) : BaseListBuilder
    {
        if(isset($this->dataGrid)) {
            throw new UI_DataGrid_Exception(
                'Cannot set list ID after initializing the grid',
                'The method may only be called before the grid has been configured.',
                self::ERROR_LIST_ALREADY_INITIALIZED
            );
        }

        $this->listID = $id;
        return $this;
    }

    public function handleActions() : BaseListBuilder
    {
        $this->getDataGrid()->executeCallbacks();

        return $this;
    }

    // endregion

    // region B - Helper methods

    public function addFilterSettings(UI_Page_Sidebar $sidebar) : self
    {
        $sidebar->addFilterSettings($this->getFilterSettings());
        return $this;
    }

    protected function renderDate(?DateTime $date=null) : string
    {
        if($date === null) {
            return UI::icon()->minus()
                ->makeMuted()
                ->setTooltip(t('No date available.'))
                ->render();
        }

        return ConvertHelper::date2listLabel(
            new Microtime($date),
            true,
            true
        );
    }

    protected function adjustLabel(string $label) : string
    {
        return str_replace('_', '_<wbr>', $label);
    }

    // endregion
}
