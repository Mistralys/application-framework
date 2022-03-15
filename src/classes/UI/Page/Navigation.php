<?php
/**
 * File containing the {@see UI_Page_Navigation} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Navigation
 */

declare(strict_types=1);

use AppUtils\Traits_Classable;
use AppUtils\Interface_Classable;

/**
 * Navigation handling class: used for the main navigation
 * and subnavigation. Can be used for other navigations as well.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_Page_Navigation extends UI_Renderable implements Interface_Classable
{
    use Traits_Classable;
    
   /**
    * @var string
    */
    private $id;

   /**
    * @var integer
    */
    private $counter = 0;

    /**
     * @var UI_Page_Navigation_Item[]
     */
    private $items = array();

   /**
    * @var string
    */
    private $append = '';
    
    /**
     * @var UI_Bootstrap_DropdownMenu|NULL
     */
    protected $contextMenu;
    
    public function __construct(UI_Page $page, string $id)
    {
        $this->id = $id;
        
        parent::__construct($page);
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Renders the navigation using the corresponding template file
     * and returns the generated HTML code.
     *
     * @return string
     * @throws Application_Exception
     */
    protected function _render() : string
    {
        if(empty($this->items)) {
            return $this->append;
        }
        
        $this->addClass('nav');
        
        $template = $this->page->createTemplate('navigation.' . $this->id);
        $template->setVar('navigation', $this);

        return $template->render().$this->append;
    }

    /**
     * Returns an indexed array with navigation items.
     *
     * @return UI_Page_Navigation_Item[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * Retrieves all navigation items from the specified group.
     * Returns an indexed array with navigation objects.
     *
     * @param string $groupName
     * @return UI_Page_Navigation_Item[]
     */
    public function getItemsByGroup(string $groupName) : array
    {
        $items = array();
        foreach ($this->items as $item) {
            if ($item->getGroup() === $groupName) {
                $items[] = $item;
            }
        }

        return $items;
    }

    public function isGroupActive(string $group) : bool
    {
        foreach ($this->items as $item)
        {
            if ($item->getGroup() !== $group)
            {
                continue;
            }

            if ($item->isActive())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a link to an internal page. Returns the new
     * navigation link item object.
     *
     * @param string $targetPageID
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @param array<string,string> $params
     * @return UI_Page_Navigation_Item_InternalLink
     */
    public function addInternalLink(string $targetPageID, $title, array $params = array()) : UI_Page_Navigation_Item_InternalLink
    {
        $this->counter++;

        $item = new UI_Page_Navigation_Item_InternalLink(
            $this,
            (string)$this->counter,
            $this->page,
            $targetPageID,
            toString($title),
            $params
        );

        $this->items[] = $item;

        return $item;
    }
    
    public function clearItems() : UI_Page_Navigation
    {
        $this->items = array();
        $this->counter = 0;
        
        return $this;
    }
    
   /**
    * Adds a subnavigation link that automatically adds
    * the page variables to the parameters (mode, submode, etc.).
    * 
    * @param string|number|UI_Renderable_Interface|NULL $title
    * @param array<string,string>|string $paramsOrURL If a URL is given, it is parsed to extract the query parameters.
    * @return UI_Page_Navigation_Item_InternalLink
    */
    public function addSubnavLink($title, $paramsOrURL=array()) : UI_Page_Navigation_Item_InternalLink
    {
        $request = Application_Driver::getInstance()->getRequest();
        $vars = Application_Admin_Skeleton::getPageParamNames();
        $params = Application_Request::resolveParams($paramsOrURL);

        foreach($vars as $var)
        {
            if(!isset($params[$var])) {
                $params[$var] = $request->getParam($var);
            }
        }
        
        return $this->addInternalLink(
            $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE],
            $title,
            $params
        );
    }

   /**
    * Adds a URL, which will be parsed automatically to add
    * its parameters.
    * 
    * @param string|number|UI_Renderable_Interface|NULL $title
    * @param string $url
    * @return UI_Page_Navigation_Item_InternalLink
    */
    public function addURL($title, string $url) : UI_Page_Navigation_Item_InternalLink
    {
        $params = Application_Request::resolveParams($url);

        return $this->addInternalLink(
            $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE],
            $title,
            $params
        );
    }

    /**
     * Adds a search box to the navigation. Use the returned
     * object to configure the widget further.
     *
     * @param callable $callback
     * @return UI_Page_Navigation_Item_Search
     * @throws Application_Exception
     */
    public function addSearch(callable $callback) : UI_Page_Navigation_Item_Search
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_Search($this, (string)$this->counter, $callback);
        $this->items[] = $item;
        
        return $item;
    }

    /**
     * Adds a dropdown menu item.
     * @param string|UI_Renderable_Interface|int|float $label
     * @return UI_Page_Navigation_Item_DropdownMenu
     * @throws Application_Exception
     */
    public function addDropdownMenu($label) : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_DropdownMenu($this, (string)$this->counter, $label);
        $this->items[] = $item;
        
        return $item;
    }
    
   /**
    * Adds an item with custom HTML code.
    * @param string|number|UI_Renderable_Interface|NULL $html
    * @return UI_Page_Navigation_Item_HTML
    */
    public function addHTML($html) : UI_Page_Navigation_Item_HTML
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_HTML($this, (string)$this->counter, $html);
        $this->items[] = $item;
        
        return $item;
    }

    /**
     * @var UI_Page_Navigation_Item|null
     */
    protected $activeItem;

    /**
     * Force any navigation item active.
     * @param UI_Page_Navigation_Item $item
     * @return UI_Page_Navigation
     */
    public function forceActiveItem(UI_Page_Navigation_Item $item) : self
    {
        $this->activeItem = $item;
        return $this;
    }
    
    public function getForcedActiveItem() : ?UI_Page_Navigation_Item
    {
        return $this->activeItem;
    }
    
   /**
    * Retrieves a navigation item by its alias (if it has any).
    * @param string $alias
    * @return UI_Page_Navigation_Item|NULL
    */
    public function getItemByAlias(string $alias) : ?UI_Page_Navigation_Item
    {
        foreach ($this->items as $item)
        {
            if($item->getAlias() === $alias)
            {
                return $item;
            }
        }

        return null;
    }
    
   /**
    * Checks whether a context menu has been set for the navigation.
    * @return boolean
    */
    public function hasContextMenu() : bool
    {
        return isset($this->contextMenu);
    }
    
   /**
    * Retrieves the context menu to show within the navigation.
    * It is created if it does not exist yet.
    * 
    * @return UI_Bootstrap_DropdownMenu
    */
    public function getContextMenu() : UI_Bootstrap_DropdownMenu
    {
        if(!isset($this->contextMenu))
        {
            $this->contextMenu = new UI_Bootstrap_DropdownMenu($this->ui);
        }
        
        return $this->contextMenu;
    }
    
   /**
    * Called when the initialization of the navigation is complete.
    * This is done automatically by the admin screen.
    */
    public function initDone() : void
    {
        foreach($this->items as $item) {
            $item->initDone();
        }
    }
    
   /**
    * Allows appending HTML right after the HTML code of the navigation.
    * 
    * @param string $html
    * @return UI_Page_Navigation
    */
    public function appendHTML(string $html) : UI_Page_Navigation
    {
        $this->append .= $html;
        
        return $this;
    }
}
