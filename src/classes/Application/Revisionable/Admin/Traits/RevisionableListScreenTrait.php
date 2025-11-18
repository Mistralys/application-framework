<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Traits;

use Application\Revisionable\Collection\BaseRevisionableDataGridMultiAction;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\Collection\RevisionableFilterSettingsInterface;
use Application\Revisionable\RevisionableInterface;
use AppUtils\ConvertHelper;
use UI_DataGrid;
use UI_DataGrid_Column;

/**
 * @see RevisionableListScreenInterface
 *
 * @property string $recordTypeName
 * @property RevisionableCollectionInterface $collection
 */
trait RevisionableListScreenTrait
{
    protected string $gridName = '';
    protected UI_DataGrid $grid;
    protected RevisionableFilterSettingsInterface $filterSettings;
    protected bool $filtersAdded = false;

    public function getURLName(): string
    {
        return RevisionableListScreenInterface::URL_NAME;
    }

    protected function _handleActions(): bool
    {
        $collection = $this->createCollection();
        $this->gridName = $collection->getRecordTypeName() . '-list';
        $this->filterSettings = $collection->getFilterSettings();

        $this->createDataGrid();

        return true;
    }

    abstract protected function getEntryData(RevisionableInterface $revisionable): array;

    protected function _renderContent(): string
    {
        $collection = $this->createCollection();
        $filters = $collection->getFilterCriteria();

        $this->grid->configure($this->filterSettings, $filters);

        $items = $filters->getItemsObjects();

        $total = count($items);
        $primaryKey = $collection->getRecordPrimaryName();
        $entries = array();
        $hasState = $this->grid->hasColumn('state');
        $hasLastModified = $this->grid->hasColumn('last_modified');
        for ($i = 0; $i < $total; $i++) {
            $item = $items[$i];
            $entry = $this->getEntryData($item);
            $entry[$primaryKey] = $item->getID();

            if ($hasState) {
                $entry['state'] = $item->requireState()->getPrettyLabel();
            }

            if ($hasLastModified) {
                $entry['last_modified'] = ConvertHelper::date2listLabel($item->getLastModifiedDate());
            }

            $entries[] = $entry;
        }

        return $this->renderDatagrid($this->getTitle(), $this->grid, $entries);
    }

    protected function createDataGrid(): void
    {
        $grid = $this->ui->createDataGrid($this->gridName);
        $this->grid = $grid;

        $grid->setFullViewTitle($this->getTitle());
        $grid->enableMultiSelect($this->createCollection()->getRecordPrimaryName());
        $grid->enableLimitOptionsDefault();

        $this->configureGrid();

        $grid->executeCallbacks();
    }

    protected function configureGrid(): void
    {
        $this->configureColumns();
        $this->configureActions();
    }

    abstract protected function configureColumns();

    abstract protected function configureActions();

    /**
     * Adds the revisionable's state to the datagrid in the <code>state</code> column.
     * @return UI_DataGrid_Column
     */
    protected function addStateColumn(): UI_DataGrid_Column
    {
        return $this->grid->addColumn('state', t('State'))->setSortable();
    }

    protected function addLastModifiedColumn(): UI_DataGrid_Column
    {
        return $this->grid->addColumn('last_modified', t('Last modified'))->setSortable();
    }

    protected function _handleSidebar(): void
    {
        $this->addFilterSettings();
    }


    protected function addFilterSettings(): void
    {
        if ($this->filtersAdded) {
            return;
        }

        $this->filtersAdded = true;

        $this->sidebar->addSeparator();
        $this->sidebar->addFilterSettings($this->filterSettings);
    }

    /**
     * @param string $className
     * @param string $label
     * @param string $redirectURL
     * @param boolean $confirm
     * @return BaseRevisionableDataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm = false): BaseRevisionableDataGridMultiAction
    {
        return $this->createCollection()->createListMultiAction(
            $className,
            $this,
            $this->grid,
            $label,
            $redirectURL,
            $confirm
        );
    }
}
