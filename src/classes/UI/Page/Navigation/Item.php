<?php
/**
 * File containing the {@link UI_Page_Navigation_Item} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Navigation_Item
 */

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;
use UI\Interfaces\TooltipableInterface;
use UI\Traits\TooltipableTrait;

/**
 * Base class for navigation items which should be extended
 * to create new specialized navigation items.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Navigation_Item
    implements
    Application_Interfaces_Iconizable,
    ClassableInterface,
    UI_Interfaces_Conditional,
    Application_Interfaces_Loggable,
    TooltipableInterface
{
    use Application_Traits_Iconizable;
    use ClassableTrait;
    use UI_Traits_Conditional;
    use Application_Traits_Loggable;
    use TooltipableTrait;
    use UI_Traits_RenderableGeneric;

    public const ITEM_POSITION_INLINE = 'inline';
    public const ITEM_POSITION_BELOW = 'below';
    
    protected Application_Request $request;
    protected UI_Page_Navigation $nav;
    protected string $id;
    protected bool $active = false;
    protected string $title = '';
    protected string $group = '';
    protected string $alias = '';
    protected UI $ui;

    /**
     * @var array<string,string|number>
     */
    protected array $params = array();

    /**
     * @var string[]
     */
    protected array $containerClasses = array();

    /**
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @throws Application_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id)
    {
        $this->nav = $nav;
        $this->id = $id;
        $this->request = Application_Request::getInstance();
        $this->ui = UI::getInstance();
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        $this->title = toString($title);
        return $this;
    }

    public function getUI() : UI
    {
        return $this->ui;
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
    * Adds a class that will be added to the navigation item's container element,
    * typically the <li> element in a list.
    * 
    * @param string $class
    * @return $this
    */
    public function addContainerClass(string $class) : self
    {
        if(!in_array($class, $this->containerClasses, true)) {
            $this->containerClasses[] = $class;
        }
        
        return $this;
    }

    /**
     * @return string[]
     */
    public function getContainerClasses() : array
    {
        return $this->containerClasses;
    }

    abstract public function getType() : string;

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
     * driver has to specify this manually as there
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

        if($active !== null && $active->getID() === $this->id)
        {
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
    public function setActive(bool $active = true) : self
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
    public function setGroup(string $title) : self
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
    * Sets an alias for the item, so it can easily be accessed later
    * using the navigation's {@see UI_Page_Navigation::getItemByAlias()}
    * method.
    * 
    * @param string $alias
    * @return $this
    * @see UI_Page_Navigation::getItemByAlias()
    */
    public function setAlias(string $alias) : self
    {
        $this->alias = $alias;
        return $this;
    }
    
    public function initDone() : void
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
