<?php

declare(strict_types=1);

namespace UI\Page\Navigation\NavConfigurator;

use UI\Page\Navigation\NavConfigurator;
use UI_Bootstrap_DropdownAnchor;
use UI_Page_Navigation_Item_DropdownMenu;

class MenuConfigurator implements \Application_Interfaces_Loggable
{
    use \Application_Traits_Loggable;

    private UI_Page_Navigation_Item_DropdownMenu $menu;
    private NavConfigurator $configurator;
    private string $logIdentifier;

    public function __construct(NavConfigurator $configurator, UI_Page_Navigation_Item_DropdownMenu $menu)
    {
        $this->configurator = $configurator;
        $this->menu = $menu;
        $this->logIdentifier = 'MenuConfigurator';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function setAutoActivate(bool $auto) : self
    {
        $this->menu->setAutoActivate($auto);
        return $this;
    }

    public function addAreaChained(string $urlName) : self
    {
        $this->addArea($urlName);
        return $this;
    }

    public function addArea(string $urlName) : ?UI_Bootstrap_DropdownAnchor
    {
        $area = $this->configurator->getAreaByURLName($urlName);

        if($area === null)
        {
            $this->log('AddArea | [%s] | Area not found.', $urlName);
            return null;
        }

        if(!$area->isUserAllowed())
        {
            $this->log('AddArea | [%s] | User not allowed.', $urlName);
            return null;
        }

        return $this->menu
            ->addLink($area->getNavigationTitle(), $area->getURL())
            ->setIcon($area->getNavigationIcon());
    }

    public function addPathChained(string $area, ?string $mode=null, ?string $submode=null, ?string $action=null) : self
    {
        $this->addPath($area, $mode, $submode, $action);
        return $this;
    }

    public function addPath(string $area, ?string $mode=null, ?string $submode=null, ?string $action=null) : ?UI_Bootstrap_DropdownAnchor
    {
        $screen = $this->configurator->getScreenByPath($area, $mode, $submode, $action);

        if($screen === null)
        {
            return null;
        }

        return $this->menu
            ->addLink($screen->getNavigationTitle(), $screen->getURL());
    }

    public function addSeparator() : self
    {
        $this->menu->addSeparator();
        return $this;
    }
}
