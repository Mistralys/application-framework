<?php

declare(strict_types=1);

use AppUtils\ArrayDataCollection;
use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURLInterface;
use UI\Bootstrap\Dropdown\AJAXLoader;
use UI\Interfaces\TooltipableInterface;
use UI\Traits\TooltipableTrait;

abstract class UI_Bootstrap_BaseDropdown
    extends UI_Bootstrap
    implements UI_Interfaces_Bootstrap_DropdownItem,
    TooltipableInterface
{
    use Application_Traits_Iconizable;
    use TooltipableTrait;
    
    protected UI_Bootstrap_DropdownMenu $menu;
    protected string $label;
    protected bool $caret = true;
    protected bool $isLink = false;
    protected bool $inNavigation = false;
    protected ?AJAXLoader $ajax = null;

    public function __construct($ui)
    {
        parent::__construct($ui);

        $this->menu = $this->ui->createDropdownMenu();
        $this->init();
    }
    
    protected function init() : void
    {
        $this->setID(nextJSID());
    }
    
   /**
    * Replaces the menu of the dropdown with the specified one.
    * 
    * @param UI_Bootstrap_DropdownMenu $menu
    * @return UI_Bootstrap_BaseDropdown
    */
    public function setMenu(UI_Bootstrap_DropdownMenu $menu) : self
    {
        $this->menu = $menu;
        return $this;
    }
    
    public function makeNavItem() : self
    {
        $this->inNavigation = true;
        return $this;
    }

    /**
     * @param string|int|float|StringableInterface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : self
    {
        $this->label = toString($label);
        return $this;
    }

   /**
    * @return UI_Bootstrap_DropdownMenu
    */
    public function getMenu() : UI_Bootstrap_DropdownMenu
    {
        return $this->menu;
    }

    /**
     * Creates and adds a new anchor menu item.
     * @param string|int|float|StringableInterface|NULL $label
     * @param string|AdminURLInterface $url
     * @return UI_Bootstrap_DropdownAnchor
     *
     * @throws UI_Exception
     */
    public function addLink($label, $url) : UI_Bootstrap_DropdownAnchor
    {
        return $this->menu->addLink($label, $url);
    }

    /**
     * Creates and adds a new anchor menu item
     * linked to the specified javascript statement.
     *
     * @param string|int|float|StringableInterface|NULL $label
     * @param string $statement
     * @return UI_Bootstrap_DropdownAnchor
     *
     * @throws UI_Exception
     */
    public function addClickable($label, string $statement) : UI_Bootstrap_DropdownAnchor
    {
        return $this->menu->addClickable($label, $statement);
    }
    
   /**
    * Adds a header to the dropdown, to group items.
    * @param string|int|float|StringableInterface|NULL $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label) : UI_Bootstrap_DropdownHeader
    {
        return $this->menu->addHeader($label);
    }
    
    public function addSeparator() : self
    {
        $this->menu->addSeparator();
        return $this;
    }

    /**
     * @param string|int|float|StringableInterface|NULL $content
     * @return UI_Bootstrap_DropdownStatic
     * @throws UI_Exception
     */
    public function addStatic($content) : UI_Bootstrap_DropdownStatic
    {
        return $this->menu->addStatic($content);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setCaretEnabled(bool $enabled) : self
    {
        $this->caret = $enabled;
        return $this;
    }

    /**
     * @return $this
     */
    public function noCaret() : self
    {
        return $this->setCaretEnabled(false);
    }
    
    protected string $layout = 'default';

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type) : self
    {
        $this->layout = $type;
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSuccess() : self
    {
        return $this->setType('success');
    }

    /**
     * @return $this
     */
    public function makeInfo() : self
    {
        return $this->setType('info');
    }
    
    public function render() : string
    {
        $this->initAJAX();

        if(!$this->menu->hasItems()) {
            return '';
        }
        
        return $this->_render();
    }

    /**
     * @param string $whichItem The name of the item to move
     * @param string $afterItem The name of the item to move it after
     * @return $this
     */
    public function moveAfter(string $whichItem, string $afterItem) : self
    {
        $this->menu->moveAfter($whichItem, $afterItem);
        return $this;
    }
    
   /**
    * Attempts to retrieve an item by its name.
    * @param string $name
    * @return UI_Interfaces_Bootstrap_DropdownItem|NULL
    */
    public function getItemByName(string $name) : ?UI_Interfaces_Bootstrap_DropdownItem
    {
        return $this->menu->getItemByName($name);
    }
    
    protected function renderCaret() : string
    {
        return '<span class="caret"></span>';
    }

    /**
     * @param string $methodName
     * @param ArrayDataCollection|null $payload Optional parameters for the AJAX call.
     * @return $this
     */
    public function makeAJAX(string $methodName, ?ArrayDataCollection $payload=null) : self
    {
        $this->ajax = new AJAXLoader($this->ui, $this->getID(), $methodName, $payload);
        return $this;
    }

    private bool $ajaxInitialized = false;

    private function initAJAX() : void
    {
        if($this->ajaxInitialized) {
            return;
        }

        $this->ajaxInitialized = true;

        if($this->ajax === null) {
            return;
        }

        $this->menu->addStatic($this->ajax->renderPlaceholder());
    }

    protected function renderContent() : string
    {
        return $this->menu->render();
    }
}
