<?php

abstract class UI_Page_Sidebar_Item extends UI_Renderable implements UI_Renderable_Interface, UI_Interfaces_Conditional, UI_Page_Sidebar_ItemInterface
{
    use UI_Traits_Conditional;
    
    /**
     * @var UI_Page_Sidebar
     */
    protected $sidebar;

    /**
     * @var UI_Page_Sidebar_ItemInterface
     */
    protected $previousSibling;
    
    /**
     * @var UI_Page_Sidebar_ItemInterface
     */
    protected $nextSibling;
    
    public function __construct(UI_Page_Sidebar $sidebar)
    {
        parent::__construct($sidebar->getPage());
        
        $this->sidebar = $sidebar;
    }

    public function createTemplate(string $templateIDOrClass) : UI_Page_Template
    {
        return $this->page->createTemplate($templateIDOrClass);
    }
    
   /**
    * Registers the position of the item in the sidebar. Called automatically
    * by the sidebar before it is rendered.
    * 
    * @param UI_Page_Sidebar_ItemInterface $prev
    * @param UI_Page_Sidebar_ItemInterface $next
    * @return UI_Page_Sidebar_Item
    * @see UI_Page_Sidebar::getItems()
    */
    public function registerPosition(UI_Page_Sidebar_ItemInterface $prev=null, UI_Page_Sidebar_ItemInterface $next=null)
    {
        $this->previousSibling = $prev;
        $this->nextSibling = $next;
        return $this;
    }
    
   /**
    * Checks whether this is a separator item.
    * @return boolean
    */
    public function isSeparator()
    {
        return $this instanceof UI_Page_Sidebar_Item_Separator;
    }
    
   /**
    * Retrieves the previous item in the sidebar before this one, if any.
    * @return UI_Page_Sidebar_ItemInterface|NULL
    */
    public function getPreviousSibling()
    {
        return $this->previousSibling;
    }
    
   /**
    * Retrieves the next item in the sidebar after this one, if any. 
    * @return UI_Page_Sidebar_ItemInterface|NULL
    */
    public function getNextSibling()
    {
        return $this->nextSibling;
    }
}

interface UI_Page_Sidebar_ItemInterface
{
    public function isSeparator();
    
   /**
    * @param UI_Page_Sidebar_ItemInterface $prev
    * @param UI_Page_Sidebar_ItemInterface $next
    * @return UI_Page_Sidebar_ItemInterface
    */
    public function registerPosition(UI_Page_Sidebar_ItemInterface $prev=null, UI_Page_Sidebar_ItemInterface $next=null);
    
    public function getPreviousSibling();
    
    public function getNextSibling();
}