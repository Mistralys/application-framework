<?php
/**
 * @package Application
 * @subpackage UserInterface
 */

declare(strict_types=1);

namespace UI\Page\Section;

use Application_EventHandler;
use Closure;
use UI;
use UI\Event\PageRendered;
use UI_Bootstrap_ButtonGroup;
use UI_Button;
use UI_Exception;
use UI_Page_Section;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

/**
 * Helper class to handle the rendering of the collapse controls
 * for a section group.
 *
 * @package Application
 * @subpackage UserInterface
 */
class GroupControls implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    private ?string $group;
    private UI_Button $btnExpand;
    private UI_Button $btnCollapse;
    private UI_Bootstrap_ButtonGroup $btnGroup;
    private string $id;
    private int $displayThreshold = 2;

    public function __construct(?string $group = null)
    {
        $this->id = 'SGC'.nextJSID();
        $this->group = $group;

        $this->btnExpand = UI::button(t('Expand all'))
            ->setIcon(UI::icon()->expand());

        $this->btnCollapse = UI::button(t('Collapse all'))
            ->setIcon(UI::icon()->collapse());

        $this->btnGroup = $this->getUI()->createButtonGroup()
            ->setID($this->id)
            ->addButton($this->btnExpand)
            ->addButton($this->btnCollapse);
    }

    public function getID() : string
    {
        return $this->id;
    }


    /**
     * Sets a CSS style for the rendered element.
     *
     * @param string $name
     * @param string|int|float|NULL $value
     * @return $this
     */
    public function setStyle(string $name, $value) : self
    {
        $this->btnGroup->setStyle($name, $value);
        return $this;
    }

    public function setDisplayThreshold(int $threshold) : self
    {
        if($threshold >= 0) {
            $this->displayThreshold = $threshold;
        }

        return $this;
    }

    public function getDisplayThreshold(): int
    {
        return $this->displayThreshold;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addClass(string $class) : self
    {
        $this->btnGroup->addClass($class);
        return $this;
    }

    /**
     * @return $this
     */
    public function makeMini() : self
    {
        $this->btnGroup->makeMini();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSmall() : self
    {
        $this->btnGroup->makeSmall();
        return $this;
    }

    /**
     * Sets / changes the name of the section group to render the controls for.
     * @param string|null $group
     * @return $this
     */
    public function setGroup(?string $group) : self
    {
        $this->group = $group;
        return $this;
    }

    public function getPlaceholder() : string
    {
        return '{GROUP_CONTROLS_'.$this->getID().'}';
    }

    public function render(): string
    {
        // Schedule the replacement of the placeholder for when
        // the whole page has been rendered. This makes it possible
        // to ensure that only sections that have been rendered in
        // the page are included in the group controls.
        Application_EventHandler::addListener(
            UI::EVENT_PAGE_RENDERED,
            Closure::fromCallable(array($this, 'onPageRendered'))
        );

        return $this->getPlaceholder();
    }

    private function onPageRendered(PageRendered $event) : void
    {
        $event->replace(
            $this->getPlaceholder(),
            $this->renderPlaceholderReplacement()
        );
    }

    private function renderPlaceholderReplacement() : string
    {
        $group = $this->group;
        if(empty($group)) {
            $group = UI_Page_Section::DEFAULT_GROUP;
        }

        $sections = SectionsRegistry::getRenderedByGroup($group);
        if(empty($sections) || count($sections) < $this->getDisplayThreshold()) {
            return '';
        }

        $this->btnGroup->addClass('section-group-controls');
        $this->btnExpand->click(UI_Page_Section::getJSExpandGroup($group));
        $this->btnCollapse->click(UI_Page_Section::getJSCollapseGroup($group));

        return (string)$this->btnGroup;
    }

    /**
     * Sets the tooltips to use for the collapse and expand buttons.
     *
     * @param string $expand
     * @param string $collapse
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltips(string $expand, string $collapse) : self
    {
        $this->btnExpand->setTooltip($expand);
        $this->btnCollapse->setTooltip($collapse);
        return $this;
    }
}
