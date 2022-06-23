<?php

declare(strict_types=1);

namespace UI\Page\Navigation;

use Application_Admin_Area;
use Application_Admin_ScreenInterface;
use Application_Driver;
use Application_Exception;
use UI\Page\Navigation\NavConfigurator\MenuConfigurator;
use UI_Bootstrap_DropdownAnchor;
use UI_Page_Navigation;
use UI_Page_Navigation_Item;
use UI_Page_Navigation_Item_DropdownMenu;

abstract class NavConfigurator
{
    public const DRIVER_CONFIGURATOR_CLASS_NAME = 'MainNavConfigurator';

    protected UI_Page_Navigation $navigation;
    protected Application_Driver $driver;

    /**
     * @var Application_Admin_Area[]
     */
    protected array $areas;

    public function __construct(Application_Driver $driver, UI_Page_Navigation $navigation)
    {
        $this->driver = $driver;
        $this->navigation = $navigation;
        $this->areas = $this->driver->getAllowedAreas();
    }

    public function getDriver() : Application_Driver
    {
        return $this->driver;
    }

    public function getNavigation() : UI_Page_Navigation
    {
        return $this->navigation;
    }

    abstract public function configure() : void;

    public function addArea(string $urlName, bool $withIcon=false) : ?UI_Page_Navigation_Item
    {
        $quickNav = $this->getAreaByURLName($urlName);

        if($quickNav === null)
        {
            return null;
        }

        $item = $this->navigation->addURL($quickNav->getNavigationTitle(), $quickNav->getURL());
        $icon = $quickNav->getNavigationIcon();

        if($withIcon && $icon !== null)
        {
            $item->setIcon($icon);
        }

        return $item;
    }

    public function getAreaByURLName(string $urlName) : ?Application_Admin_Area
    {
        foreach($this->areas as $area)
        {
            if($area->getURLName() === $urlName)
            {
                return $area;
            }
        }

        return null;
    }

    /**
     * @var array<string,MenuConfigurator>
     */
    private array $menus = array();

    public function addMenu($label) : MenuConfigurator
    {
        if(!isset($this->menus[$label]))
        {
            $this->menus[$label] = new MenuConfigurator(
                $this,
                $this->navigation->addDropdownMenu($label)
            );
        }

        return $this->menus[$label];
    }

    public function getScreenByPath(string $area, ?string $mode=null, ?string $submode=null, ?string $action=null) : ?Application_Admin_ScreenInterface
    {
        $path = array($area);

        if(!empty($mode)) {
            $path[] = $mode;
            if(!empty($submode)) {
                $path[] = $submode;
                if(!empty($action)) {
                    $path[] = $action;
                }
            }
        }

        return $this->getDriver()->getScreenByPath(implode('.', $path));
    }
}
