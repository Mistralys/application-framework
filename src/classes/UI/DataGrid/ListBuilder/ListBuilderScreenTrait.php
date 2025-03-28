<?php

declare(strict_types=1);

namespace UI\DataGrid\ListBuilder;

use UI_Themes_Theme_ContentRenderer;

/**
 * @see ListBuilderScreenInterface
 */
trait ListBuilderScreenTrait
{
    abstract protected function _handleSidebarTop() : void;
    abstract protected function _handleSidebarBottom() : void;

    protected function _handleSidebar(): void
    {
        $this->_handleSidebarTop();

        $settings = $this->grid->getFilterSettings();
        if($settings !== null) {
            $this->sidebar->addFilterSettings($settings);
        }

        $this->_handleSidebarBottom();
    }

    protected function _handleActions(): bool
    {
        $this->_handleCustomActions();

        $this->grid = $this->createListBuilder()
            ->setListID($this->getListID());

        return true;
    }

    abstract protected function _handleCustomActions() : void;

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->grid)
            ->makeWithSidebar();
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubmode() : string
    {
        return '';
    }
}
