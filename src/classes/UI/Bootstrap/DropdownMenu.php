<?php

use AppUtils\ConvertHelper;

class UI_Bootstrap_DropdownMenu extends UI_Bootstrap
{
   /**
    * @var UI_Interfaces_Bootstrap[]
    */
    protected $items = array();

    /**
     * @var bool
     */
    private $left;

    protected function _render()
    {
        if(empty($this->items)) {
            return '';
        }

        if($this->left) {
            $this->addClass('pull-right');
        }

        $this->addClass('dropdown-menu');
        $this->setAttribute('class', implode(' ', $this->classes));
        
        $atts = ConvertHelper::array2attributeString($this->attributes);
        
        ob_start();
?>
<!-- start menu -->
<ul <?php echo $atts ?>>
	<?php 
	    foreach ($this->items as $item) 
        {
           echo $item->render();
        }   
    ?>
</ul>
<!-- end menu -->
<?php 
        
        return ob_get_clean();
    }

    /**
     * Makes the menu open on the left side of the toggle,
     * instead of the default right side.
     *
     * @return $this
     */
    public function openLeft()
    {
        $this->left = true;
        return $this;
    }

    /**
    * Adds a submenu item: creates the menu instance
    * and returns it to be configured.
    * 
    * @param string $label The label of the submenu item
    * @return UI_Bootstrap_DropdownSubmenu
    */
    public function addMenu($label)
    {
        $menu = $this->ui->createDropdownSubmenu($label);
        $menu->setMenu($this);
        
        $this->items[] = $menu;
        
        return $menu;
    }

    /**
     * Adds a menu item that links to a regular URL.
     * 
     * @param string $label
     * @param string $url
     * @return UI_Bootstrap_DropdownAnchor
     */
    public function addLink($label, $url)
    {
        $link = $this->ui->createDropdownAnchor($label);
        $link->setHref($url);
        $link->addClass('menu-link');
        $link->addClass('dropdown-item');
        $this->items[] = $link;

        return $link;
    }
    
   /**
    * Whether the menu has items.
    * @return boolean
    */
    public function hasItems()
    {
        return !empty($this->items);
    }
    
   /**
    * Adds a subheader within the menu.
    * @param string $label
    * @return UI_Bootstrap_DropdownHeader
    */
    public function addHeader($label)
    {
        $header = $this->ui->createDropdownHeader($label);
        $this->items[] = $header;
        return $header;
    }
    
   /**
    * Adds a menu item that executes the specified javascript
    * statement when clicked.
    * 
    * @param string $label
    * @param string $statement
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function addClickable($label, $statement)
    {
        $link = $this->ui->createDropdownAnchor($label);
        $link->setOnclick($statement);
        $link->addClass('menu-clickable');
        $link->addClass('dropdown-item');
        $this->items[] = $link;
        
        return $link;
    }

    /**
     * @return $this
     * @throws Application_Exception
     */
    public function addSeparator()
    {
        if(empty($this->items)) {
            return $this;
        }
        
        $items = $this->items;
        $last = array_pop($items);
        
        if(!$last instanceof UI_Bootstrap_DropdownDivider) {
            $this->items[] = $this->ui->createBootstrap('DropdownDivider');
        }
        
        return $this;
    }
    
    public function addStatic($content)
    {
        $item = $this->ui->createDropdownStatic($content);
        $this->items[] = $item;
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
    public function moveAfter($whichItem, $afterItem)
    {
        $moveItem = $this->getItemByName($whichItem);
        if(!$moveItem) {
            return $this;
        }
        
        $keep = array();
        foreach($this->items as $item) {
            if($item->isNamed($whichItem)) {
                continue;
            }
            
            $keep[] = $item;
            
            if($item->isNamed($afterItem)) {
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
    * @return UI_Interfaces_Bootstrap|NULL
    */
    public function getItemByName($name)
    {
        foreach($this->items as $item) {
            if($item->isNamed($name)) {
                return $item;
            }
        }
        
        return null;
    }
}

