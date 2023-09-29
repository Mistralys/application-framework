<?php

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\Interface_Stringable;
use AppUtils\OutputBuffering;

class UI_Bootstrap_DropdownMenu extends UI_Bootstrap
{
   /**
    * @var UI_Interfaces_Bootstrap_DropdownItem[]
    */
    protected array $items = array();

    private bool $left = false;

    protected function _render() : string
    {
        if(empty($this->items))
        {
            return '';
        }

        if($this->left)
        {
            $this->addClass('pull-right');
        }

        $this->addClass('dropdown-menu');
        $this->setAttribute('class', implode(' ', $this->classes));
        
        $attributes = ConvertHelper::array2attributeString($this->attributes);
        
        OutputBuffering::start();
        ?>
        <!-- start menu -->
        <ul <?php echo $attributes ?>>
            <?php
                foreach ($this->items as $item)
                {
                   echo $item->render();
                }
            ?>
        </ul>
        <!-- end menu -->
        <?php
        
        return OutputBuffering::get();
    }

    /**
     * Makes the menu open on the left side of the toggle,
     * instead of the default right side.
     *
     * @return $this
     */
    public function openLeft() : self
    {
        $this->left = true;
        return $this;
    }

    private function addItem(UI_Interfaces_Bootstrap_DropdownItem $item) : void
    {
        $this->items[] = $item;
    }

    /**
     * Adds a submenu item: creates the menu instance
     * and returns it to be configured.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label The label of the submenu item
     * @return UI_Bootstrap_DropdownSubmenu
     * @throws UI_Exception
     */
    public function addMenu($label) : UI_Bootstrap_DropdownSubmenu
    {
        $menu = $this->ui
            ->createDropdownSubmenu($label)
            ->setMenu($this);

        $this->addItem($menu);

        return $menu;
    }

    /**
     * Adds a menu item that links to a regular URL.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $url
     * @return UI_Bootstrap_DropdownAnchor
     * @throws UI_Exception
     */
    public function addLink($label, string $url) : UI_Bootstrap_DropdownAnchor
    {
        $link = $this->ui
            ->createDropdownAnchor($label)
            ->addClass('menu-link')
            ->setHref($url);

        $this->addItem($link);

        return $link;
    }
    
   /**
    * Whether the menu has items.
    * @return boolean
    */
    public function hasItems() : bool
    {
        return !empty($this->items);
    }
    
   /**
    * Adds a subheader within the menu.
    * @param string|number|UI_Renderable_Interface|NULL $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label) : UI_Bootstrap_DropdownHeader
    {
        $header = $this->ui->createDropdownHeader($label);

        $this->addItem($header);

        return $header;
    }

    /**
     * Adds a menu item that executes the specified javascript
     * statement when clicked.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $statement
     * @return UI_Bootstrap_DropdownAnchor
     * @throws UI_Exception
     */
    public function addClickable($label, string $statement) : UI_Bootstrap_DropdownAnchor
    {
        $link = $this->ui
            ->createDropdownAnchor($label)
            ->setOnclick($statement)
            ->addClass('menu-clickable');

        $this->addItem($link);
        
        return $link;
    }

    /**
     * @return $this
     * @throws Application_Exception
     */
    public function addSeparator() : self
    {
        if(empty($this->items))
        {
            return $this;
        }
        
        $items = $this->items;
        $last = array_pop($items);
        
        if(!$last instanceof UI_Bootstrap_DropdownDivider)
        {
            $this->addItem($this->createDivider());
        }
        
        return $this;
    }

    /**
     * @return UI_Bootstrap_DropdownDivider
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    private function createDivider() : UI_Bootstrap_DropdownDivider
    {
        return ClassHelper::requireObjectInstanceOf(
            UI_Bootstrap_DropdownDivider::class,
            $this->ui->createBootstrap('DropdownDivider')
        );
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $content
     * @return UI_Bootstrap_DropdownStatic
     * @throws UI_Exception
     */
    public function addStatic($content) : UI_Bootstrap_DropdownStatic
    {
        $item = $this->ui->createDropdownStatic($content);

        $this->addItem($item);

        return $item;
    }

   /**
    * Moves the specified item directly after another item
    * in the menu, by their names. Note: you have to set the
    * names of the items for this to work.
    * 
    * @param string $whichItem The name of the item to move
    * @param string $afterItem The name of the item to move it after
    * @return UI_Bootstrap_DropdownMenu
    */
    public function moveAfter(string $whichItem, string $afterItem) : self
    {
        $moveItem = $this->getItemByName($whichItem);
        if(!$moveItem)
        {
            return $this;
        }
        
        $keep = array();
        foreach($this->items as $item)
        {
            if($item->isNamed($whichItem))
            {
                continue;
            }
            
            $keep[] = $item;
            
            if($item->isNamed($afterItem))
            {
                $keep[] = $moveItem;
            }
        }
        
        $this->items = $keep;
        
        return $this;
    }
    
   /**
    * Retrieves a menu item by its name.
    * 
    * @param string $name
    * @return UI_Interfaces_Bootstrap_DropdownItem|NULL
    */
    public function getItemByName(string $name) : ?UI_Interfaces_Bootstrap_DropdownItem
    {
        foreach($this->items as $item)
        {
            if($item->isNamed($name))
            {
                return $item;
            }
        }
        
        return null;
    }
}
