<?php

class UI_Page_Navigation_Item_DropdownMenu extends UI_Page_Navigation_Item
{
   /**
    * @var UI_Bootstrap_DropdownMenu
    */
    protected $menu;
    
    protected $label;
    
    protected $active = false;
    
    public function __construct(UI_Page_Navigation $nav, $id, $label)
    {
        parent::__construct($nav, $id);
        
        $this->label = $label;
        $this->menu = UI::getInstance()->createDropdownMenu();
    }
    
    protected $split = false;
    
   /**
    * Creates a split button for the menu, the menu itself
    * opening by clicking the caret, and the main button label
    * linking to its own destination.
    * 
    * Use the {@link link()} or {@link click()} methods to
    * set the target of the button.
    * 
    * @return UI_Page_Navigation_Item_DropdownMenu
    * 
    */
    public function makeSplit()
    {
        $this->split = true;
        return $this;
    }
    
    protected $link;
    
   /**
    * Links the menu button to its own URL. Automatically
    * turns the button into a split button with the caret
    * used to access the menu.
    * 
    * @param string $url
    * @return UI_Page_Navigation_Item_DropdownMenu
    */
    public function link($url)
    {
        $this->makeSplit();
        $this->link = $url;
        return $this;
    }

    protected $click;
    
    /**
     * Links the menu button to its own javascript statement. 
     * Automatically turns the button into a split button with 
     * the caret used to access the menu.
     *
     * @param string $statement
     * @return UI_Page_Navigation_Item_DropdownMenu
     */
    public function click($statement)
    {
        $this->makeSplit();
        $this->click = $statement;
        return $this;
    }
    
    public function getType()
    {
        return 'dropdownmenu';
    }
    
    public function makeActive()
    {
        $this->active = true;
    }

    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $this->addClass('dropdown');
        
        if($this->active) {
            $this->addClass('active');
        }
        
        $classes = $this->classes;
        
        if($this->split && (isset($this->link) || isset($this->click) )) {
            $linkAtts = array(
                'href' => 'javascript:void(0)',
                'class' => 'dropdown-toggle split-link',
            );
            
            if(isset($this->link)) {
                $linkAtts['href'] = $this->link;
            } else {
                $linkAtts['onclick'] = $this->click;
            }
            
            $html = 
            '<li class="'.implode(' ', $classes).'">'.
                '<a'.compileAttributes($linkAtts).'>'.
                    $this->label . ' ' .
                '</a>'.
                '<a href="#" class="dropdown-toggle split-caret" data-toggle="dropdown">'.
                    '<b class="caret"></b>'.
                '</a>'.
                $this->menu->render().
            '</li>';
            
            return $html;
        }
        
        return 
        '<li class="'.implode(' ', $classes).'">'.
            '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.
                $this->label . ' ' .
                '<b class="caret"></b>'.
            '</a>'.
            $this->menu->render().
        '</li>';
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
        return $this->menu->addLink($label, $url);
    }
    
    public function addSeparator()
    {
        return $this->menu->addSeparator();
    }
}
