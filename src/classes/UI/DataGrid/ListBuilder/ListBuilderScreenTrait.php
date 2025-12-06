<?php
/**
 * @package User Interface
 * @subpackage List Builder
 */

declare(strict_types=1);

namespace UI\DataGrid\ListBuilder;

use UI\Interfaces\ListBuilderInterface;
use UI_Themes_Theme_ContentRenderer;

/**
 * Trait used to help implement the {@see ListBuilderScreenInterface}.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenInterface
 */
trait ListBuilderScreenTrait
{
    abstract protected function _handleSidebarTop() : void;
    abstract protected function _handleSidebarBottom() : void;

    protected function _handleSidebar(): void
    {
        $this->_handleSidebarTop();

        $settings = $this->getBuilder()->getFilterSettings();
        if($settings !== null) {
            $this->sidebar->addFilterSettings($settings);
        }

        $this->_handleSidebarBottom();
    }

    protected ?ListBuilderInterface $grid = null;

    public function getBuilder() : ListBuilderInterface
    {
        if(!isset($this->grid)) {
            $this->grid = $this->createListBuilder()
                ->setListID($this->getListID());
        }

        return $this->grid;
    }

    protected function _handleActions(): bool
    {
        $this->_handleCustomActions();

        return true;
    }

    abstract protected function _handleCustomActions() : void;

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $builder = $this->getBuilder();

        return $this->renderer
            ->appendContent($this->_renderAboveList($builder))
            ->appendContent($builder)
            ->appendContent($this->_renderBelowList($builder))
            ->makeWithSidebar();
    }

    protected function _renderAboveList(ListBuilderInterface $builder): string
    {
        return '';
    }

    protected function _renderBelowList(ListBuilderInterface $builder): string
    {
        return '';
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
