<?php

class UI_Page_Navigation_Item_DropdownMenu extends UI_Page_Navigation_Item
{
   /**
    * @var UI_Bootstrap_DropdownMenu
    */
    protected $menu;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var bool
     */
    protected $split = false;

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var string
     */
    protected $click = '';

    /**
     * UI_Page_Navigation_Item_DropdownMenu constructor.
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param string|UI_Renderable_Interface|int|float $label
     * @throws Application_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id, $label)
    {
        parent::__construct($nav, $id);
        
        $this->menu = UI::getInstance()->createDropdownMenu();
        $this->setLabel($label);
    }

    /**
     * @param string|UI_Renderable_Interface|int|float $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = toString($label);
        return $this;
    }

   /**
    * Creates a split button for the menu, the menu itself
    * opening by clicking the caret, and the main button label
    * linking to its own destination.
    * 
    * Use the {@link link()} or {@link click()} methods to
    * set the target of the button.
    * 
    * @return $this
    */
    public function makeSplit()
    {
        $this->split = true;
        return $this;
    }
    
   /**
    * Links the menu button to its own URL. Automatically
    * turns the button into a split button with the caret
    * used to access the menu.
    * 
    * @param string $url
    * @return UI_Page_Navigation_Item_DropdownMenu
    */
    public function link(string $url)
    {
        $this->makeSplit();
        $this->link = $url;
        return $this;
    }

    /**
     * Links the menu button to its own javascript statement. 
     * Automatically turns the button into a split button with 
     * the caret used to access the menu.
     *
     * @param string $statement
     * @return UI_Page_Navigation_Item_DropdownMenu
     */
    public function click(string $statement)
    {
        $this->makeSplit();
        $this->click = $statement;
        return $this;
    }
    
    public function getType()
    {
        return 'dropdownmenu';
    }

    /**
     * Makes this the active menu item.
     * @return $this
     */
    public function makeActive()
    {
        $this->active = true;
        return $this;
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
        
        if($this->split && (!empty($this->link) || !empty($this->click) )) {
            $linkAtts = array(
                'href' => 'javascript:void(0)',
                'class' => 'dropdown-toggle split-link',
            );
            
            if(!empty($this->link)) {
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
    public function addLink(string $label, string $url)
    {
        return $this->menu->addLink($label, $url);
    }

    /**
     * @return UI_Bootstrap_DropdownMenu
     * @throws Application_Exception
     */
    public function addSeparator()
    {
        return $this->menu->addSeparator();
    }
}
