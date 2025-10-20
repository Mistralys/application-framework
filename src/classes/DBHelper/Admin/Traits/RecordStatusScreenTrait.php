<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper_BaseRecord;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

trait RecordStatusScreenTrait
{
    public function getURLName() : string
    {
        return RecordStatusScreenInterface::URL_NAME;
    }

    public function getNavigationTitle() : string
    {
        return t('Status');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getRecordStatusURL());
    }

    abstract protected function getRecordStatusURL() : string|AdminURLInterface;

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->createPropertiesGrid())
            ->makeWithSidebar();
    }

    private function createPropertiesGrid(): UI_PropertiesGrid
    {
        $grid = $this->getUI()->createPropertiesGrid();

        $this->_populateGrid($grid, $this->getRecord());

        return $grid;
    }

    abstract protected function _populateGrid(UI_PropertiesGrid $grid, DBHelper_BaseRecord $record): void;
}
