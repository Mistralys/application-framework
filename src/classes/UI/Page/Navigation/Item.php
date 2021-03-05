<?php
/**
 * File containing the {@link UI_Page_Navigation_Item} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Navigation_Item
 */

use AppUtils\Interface_Classable;
use AppUtils\Traits_Classable;

/**
 * Base class for navigation items which should be extended
 * to create new specialized navigation items.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method UI_Page_Navigation_Item addClass($class) addClass(string $class)
 */
abstract class UI_Page_Navigation_Item implements Application_Interfaces_Iconizable, Interface_Classable, UI_Interfaces_Conditional, Application_Interfaces_Loggable
{
    use Application_Traits_Iconizable;
    use Traits_Classable;
    use UI_Traits_Conditional;
    use Application_Traits_Loggable;

    const ITEM_POSITION_INLINE = 'inline';
    const ITEM_POSITION_BELOW = 'below';
    
    /**
     * @var Application_Request
     */
    protected $request;
    
    /**
     * @var UI_Page_Navigation
     */
    protected $nav;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var string
     */
    protected $title = '';

    protected $params = array();

    /**
     * @var string
     */
    protected $group = '';

    /**
     * @var string
     */
    protected $alias = '';

   /**
    * @var UI
    */
    protected $ui;

    /**
     * @param UI_Page_Navigation $nav
     * @param string|int $id
     * @throws Application_Exception
     */
    public function __construct(UI_Page_Navigation $nav, $id)
    {
        $this->nav = $nav;
        $this->id = strval($id);
        $this->request = Application_Request::getInstance();
        $this->ui = UI::getInstance();
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getAlias() : string
    {
        return $this->alias;
    }

    /**
     * Retrieves the positioning of the item. Some items may
     * be positioned directly below the navigation, while the
     * default is within the navigation.
     *
     * @return string
     *
     * @see UI_Page_Navigation_Item::ITEM_POSITION_BELOW
     * @see UI_Page_Navigation_Item::ITEM_POSITION_INLINE
     */
    public function getPosition() : string
    {
        return self::ITEM_POSITION_INLINE;
    }

    /**
     * Whether the item is placed below the navigation.
     * @return bool
     */
    public function isPositionBelow() : bool
    {
        return $this->getPosition() === self::ITEM_POSITION_BELOW;
    }

    /**
     * @var string[]
     */
    protected $containerClasses = array();
    
   /**
    * Adds a class that will be added to the navigation item's container element,
    * typically the <li> element in a list.
    * 
    * @param string $class
    * @return $this
    */
    public function addContainerClass(string $class)
    {
        if(!in_array($class, $this->containerClasses)) {
            $this->containerClasses[] = $class;
        }
        
        return $this;
    }
    
    public function getContainerClasses()
    {
        return $this->containerClasses;
    }

    abstract public function getType();

    /**
     * @param array<string,string> $attributes
     * @return string
     */
    abstract public function render(array $attributes = array()) : string;

    /**
     * Checks whether the current navigation item is
     * the active navigation item.
     *
     * Note: this is not detected automatically, your
     * driver has to specifiy this manually as there
     * is no way for navigation items to know this
     * for themselves (unless you extend an existing
     * navigation item and add this functionality for
     * your application).
     *
     * @return boolean
     * @see setActive()
     */
    public function isActive() : bool
    {
        $active = $this->nav->getForcedActiveItem();
        if($active && $active->getID() == $this->id) {
            return true;
        }
        
        return $this->active;
    }

    /**
     * Sets the current navigation item to the specified
     * active state, or to active if not specified.
     *
     * @param bool $active
     * @throws InvalidArgumentException
     * @see isActive()
     * @return $this
     */
    public function setActive(bool $active = true)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Invalid value for setActive, boolean expected, ' . gettype($active) . ' given.');
        }

        $this->active = $active;
        return $this;
    }

    /**
     * Sets the group for the navigation element: grouped elements
     * are displayed as a sub-menu with items, the title being the
     * label of the menu.
     *
     * @param string $title
     * @return $this
     */
    public function setGroup(string $title)
    {
        $this->group = $title;
        return $this;
    }

    /**
     * The title of the group the navigation element should be filed under.
     *
     * @return string
     */
    public function getGroup() : string
    {
        return $this->group;
    }

   /**
    * Sets an alias for the item so it can easily be accessed later
    * using the navigation's getByAlias() method.
    * 
    * @param string $alias
    * @return UI_Page_Navigation_Item
    * @see UI_Page_Navigation::getByAlias()
    */
    public function setAlias(string $alias)
    {
        $this->alias = $alias;
        return $this;
    }
    
    public function initDone()
    {
        // can be extended by the items
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'NavItem [%s #%s]',
            $this->getType(),
            $this->getID()
        );
    }
}
