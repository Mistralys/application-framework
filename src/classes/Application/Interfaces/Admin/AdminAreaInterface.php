<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use UI_Icon;
use UI_Page_Navigation;
use UI_Page_Navigation_Item;

interface AdminAreaInterface extends AdminScreenInterface
{
    /**
     * @return class-string<AdminScreenInterface>|string
     */
    public function getDefaultMode() : string;

    public function getNavigationGroup() : string;

    /**
     * Retrieves the names of administration areas this
     * area depends on, and which may not be disabled as
     * a result when this is enabled.
     *
     * @return string[] The URL names of the areas
     */
    public function getDependencies() : array;

    /**
     * Whether this administration area is a core area that
     * cannot be disabled in the application sets.
     *
     * @return boolean
     */
    public function isCore() : bool;

    /**
     * Checks whether this page has different modes (sub- administration sections).
     * @return boolean
     */
    public function hasModes() : bool;

    /**
     * Adds the page to the main navigation.
     * @param UI_Page_Navigation $nav
     * @return UI_Page_Navigation_Item|null
     */
    public function addToNavigation(UI_Page_Navigation $nav) : ?UI_Page_Navigation_Item;

    /**
     * Retrieves the active mode object if any.
     * @return AdminModeInterface|NULL
     */
    public function getMode() : ?AdminModeInterface;

    /**
     * Retrieves the active submode object, if any.
     *
     * @return AdminSubmodeInterface|NULL
     */
    public function getSubmode() : ?AdminSubmodeInterface;

    /**
     * Retrieves the active action object if any.
     * @return AdminActionInterface|NULL
     */
    public function getAction() : ?AdminActionInterface;

    /**
     * Sets up the available UI elements, by calling
     * the according <code>handleXXX</code> methods.
     * These recurse into any subscreens as applicable.
     *
     * @see Application_Traits_Admin_Screen::handleSidebar()
     * @see Application_Traits_Admin_Screen::handleSubnavigation()
     * @see Application_Traits_Admin_Screen::handleBreadcrumb()
     * @see Application_Traits_Admin_Screen::handleHelp()
     * @see Application_Traits_Admin_Screen::handleContextMenu()
     * @see Application_Traits_Admin_Screen::handleTabs()
     */
    public function handleUI() : void;

    /**
     * Returns an optional icon object which will be used to display
     * the area in the main navigation.
     *
     * @return UI_Icon|NULL
     */
    public function getNavigationIcon() : ?UI_Icon;

    /**
     * Retrieves all areas this area depends on to work.
     *
     * @return AdminAreaInterface[]
     */
    public function getDependentAreas() : array;

    /**
     * Starts the main user interface using this area.
     * @return void
     */
    public function startUI() : void;
}
