<?php
/**
 * File containing the {@see UI_Page_Navigation} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Navigation
 */

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

    private $items = array();

   /**
    * @var string
    */
    private $append = '';
    
    protected $classes = array();
    
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
    public function getID()
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
    protected function _render()
    {
        if(empty($this->items)) {
            return ''.$this->append;
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
    public function getItems()
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
    public function getItemsByGroup($groupName)
    {
        $items = array();
        foreach ($this->items as $item) {
            if ($item->getGroup() == $groupName) {
                $items[] = $item;
            }
        }

        return $items;
    }

    public function isGroupActive($group)
    {
        foreach ($this->items as $item) {
            if ($item->getGroup() != $group) {
                continue;
            }

            if ($item->isActive()) {
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
     * @param string $title
     * @param array $params
     * @return UI_Page_Navigation_Item_InternalLink
     */
    public function addInternalLink($targetPageID, $title, $params = array())
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_InternalLink($this, $this->counter, $this->page, $targetPageID, $title, $params);
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
    * the page variables to the parameters (mode, submode, etc).
    * 
    * @param string $title
    * @param array|string $paramsOrURL If an URL is given, it is parsed to extract the query parameters.
    * @return UI_Page_Navigation_Item_InternalLink
    */
    public function addSubnavLink($title, $paramsOrURL=array())
    {
        $params = $paramsOrURL;
        
        if(is_string($paramsOrURL)) {
            $url = str_replace('&amp;', '&', $paramsOrURL);
            $params = array();
            $query = parse_url($url, PHP_URL_QUERY);

            if(!empty($query)) {
                $params = \AppUtils\ConvertHelper::parseQueryString($query);
            }
        }
        
        $request = Application_Driver::getInstance()->getRequest();
        $vars = array('page', 'mode', 'submode', 'action');
        foreach($vars as $var) {
            if(!isset($params[$var])) {
                $params[$var] = $request->getParam($var);
            }
        }
        
        return $this->addInternalLink($params['page'], $title, $params);
    }
    
   /**
    * Adds an URL, which will be parsed automatically to add
    * its parameters.
    * 
    * @param string $title
    * @param string $url
    * @return UI_Page_Navigation_Item_InternalLink
    */
    public function addURL($title, $url)
    {
        $url = str_replace('&amp;', '&', $url);
        $params = array();
        $query = parse_url($url, PHP_URL_QUERY);

        if(!empty($query)) {
            $params = \AppUtils\ConvertHelper::parseQueryString($query);
        }
        
        return $this->addInternalLink($params['page'], $title, $params);
    }

    /**
     * Adds a search box to the navigation. Use the returned
     * object to configure the widget further.
     *
     * @param callable $callback
     * @return UI_Page_Navigation_Item_Search
     * @throws Application_Exception
     */
    public function addSearch($callback) : UI_Page_Navigation_Item_Search
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_Search($this, strval($this->counter), $callback);
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
        $item = new UI_Page_Navigation_Item_DropdownMenu($this, strval($this->counter), $label);
        $this->items[] = $item;
        
        return $item;
    }
    
   /**
    * Adds an item with custom HTML code.
    * @param string $html
    * @return UI_Page_Navigation_Item_HTML
    */
    public function addHTML($html)
    {
        $this->counter++;
        $item = new UI_Page_Navigation_Item_HTML($this, $this->counter, $html);
        $this->items[] = $item;
        
        return $item;
    }

    protected $activeItem = null;
    
   /**
    * Force any navigation item active.
    * @param UI_Page_Navigation_Item $item
    */
    public function forceActiveItem(UI_Page_Navigation_Item $item)
    {
        $this->activeItem = $item;
    }
    
    public function getForcedActiveItem()
    {
        return $this->activeItem;
    }
    
   /**
    * Retrieves a navigation item by its alias (if it has any).
    * @param string $alias
    * @return UI_Page_Navigation_Item|NULL
    */
    public function getItemByAlias($alias)
    {
        $total = count($this->items);
        for($i=0; $i<$total; $i++) {
            $item = $this->items[$i];
            if($item->getAlias()==$alias) {
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
        if(!isset($this->contextMenu)) {
            $this->contextMenu = new UI_Bootstrap_DropdownMenu($this->ui);
        }
        
        return $this->contextMenu;
    }
    
   /**
    * Called when the initialization of the navigation is complete.
    * This is done automatically by the admin screen.
    */
    public function initDone()
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
