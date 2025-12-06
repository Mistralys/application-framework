<?php
/**
 * File containing the {@see UI_Page_Navigation} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Navigation
 */

declare(strict_types=1);

use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;
use UI\AdminURLs\AdminURLInterface;

/**
 * Navigation handling class: used for the main navigation
 * and subnavigation. Can be used for other navigations as well.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_Page_Navigation extends UI_Renderable implements ClassableInterface
{
    use ClassableTrait;

    private string $id;
    private int $counter = 0;
    private string $append = '';
    protected ?UI_Bootstrap_DropdownMenu $contextMenu = null;

    /**
     * @var UI_Page_Navigation_Item[]
     */
    private array $items = array();
    private string $templateID;
    private bool $initDone = false;

    public function __construct(UI_Page $page, string $id)
    {
        $this->id = $id;
        $this->templateID = 'navigation.' . $id;
        
        parent::__construct($page);
    }

    public function getTemplateID(): string
    {
        return $this->templateID;
    }

    public static function create(string $id, ?UI_Page $page=null) : UI_Page_Navigation
    {
        if($page === null) {
            $page = UI::getInstance()->getPage();
        }

        return new self($page, $id);
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Whether there are any items in the navigation.
     *
     * NOTE: Does NOT check whether the items are valid.
     * Use {@see UI_Page_Navigation::hasValidItems()}
     * instead if this is relevant.
     *
     * @return bool
     */
    public function hasItems() : bool
    {
        return !empty($this->items);
    }

    /**
     * Checks whether the navigation has any items that
     * are valid to be displayed (that fulfill all conditions
     * that may have been defined for them, see {@see UI_Interfaces_Conditional}).
     *
     * @return bool
     */
    public function hasValidItems() : bool
    {
        if(!$this->hasItems())
        {
            return false;
        }

        foreach($this->items as $item)
        {
            if($item->isValid())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @param string $jsStatement
     * @return UI_Page_Navigation_Item_Clickable
     * @throws Application_Exception
     */
    public function addClickable($title, string $jsStatement) : UI_Page_Navigation_Item_Clickable
    {
        $this->counter++;

        $item = new UI_Page_Navigation_Item_Clickable(
            $this,
            (string)$this->counter,
            $title,
            $jsStatement
        );

        $this->items[] = $item;

        return $item;
    }

    /**
     * @param string|class-string<UI_Page_Template> $templateID
     * @return $this
     */
    public function setTemplateID(string $templateID) : self
    {
        $this->templateID = $templateID;
        return $this;
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
        $this->initDone();

        if(empty($this->items)) {
            return $this->append;
        }
        
        $this->addClass('nav');
        
        $template = $this->page->createTemplate($this->getTemplateID());
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
        return $this->getActiveGroupItem($group) !== null;
    }

    public function getActiveGroupItem(string $group) : ?UI_Page_Navigation_Item
    {
        foreach ($this->items as $item)
        {
            if ($item->getGroup() !== $group)
            {
                continue;
            }

            if ($item->isActive())
            {
                return $item;
            }
        }

        return null;
    }

    /**
     * Adds a link to an internal page. Returns the new
     * navigation link item object.
     *
     * @param string $targetPageID
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @param array<string,string> $params
     * @return UI_Page_Navigation_Item_InternalLink
     *
     * @throws Application_Exception
     * @throws UI_Exception
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

    /**
     * @param string $title
     * @param string|AdminURLInterface $url
     * @return UI_Page_Navigation_Item_ExternalLink
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function addExternalLink(string $title, $url) : UI_Page_Navigation_Item_ExternalLink
    {
        $this->counter++;

        $item = new UI_Page_Navigation_Item_ExternalLink(
            $this,
            (string)$this->counter,
            $url,
            $title
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
    * @param array<string,string>|AdminURLInterface|string $paramsOrURL If a URL is given, it is parsed to extract the query parameters.
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
            $params[AdminScreenInterface::REQUEST_PARAM_PAGE],
            $title,
            $params
        );
    }

   /**
    * Adds a URL, which will be parsed automatically to add
    * its parameters.
    * 
    * @param string|number|UI_Renderable_Interface|NULL $title
    * @param string|AdminURLInterface $url
    * @return UI_Page_Navigation_Item_InternalLink|UI_Page_Navigation_Item_ExternalLink
    */
    public function addURL($title, $url) : UI_Page_Navigation_Item
    {
        $params = Application_Request::resolveParams($url);

        if(isset($params[AdminScreenInterface::REQUEST_PARAM_PAGE]))
        {
            return $this->addInternalLink(
                $params[AdminScreenInterface::REQUEST_PARAM_PAGE],
                $title,
                $params
            );
        }

        return $this->addExternalLink($title, $url);
    }

    /**
     * Adds a search box to the navigation. Use the returned
     * object to configure the widget further.
     *
     * The callback gets the following parameters:
     *
     * 1. The search item instance (@see UI_Page_Navigation_Item_Search)
     * 2. The search term string
     * 3. The scope string (if applicable)
     * 4. The country name (if applicable)
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

    protected ?UI_Page_Navigation_Item $activeItem = null;

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
        if($this->initDone) {
            return;
        }

        $this->initDone = true;

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
