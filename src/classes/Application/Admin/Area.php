<?php

abstract class Application_Admin_Area extends Application_Admin_Skeleton
{
    use Application_Traits_Admin_Screen;

    public const NAV_AREA_QUICK_NAVIGATION = 'area-quick-nav';

    public function __construct(Application_Driver $driver, bool $adminMode = false)
    {
        // we do as if the UI had already been started to avoid loading it yet,
        // it is started manually for areas. 
        $this->uiStarted = true;
        
        // set the admin mode: this will be inherited all the way
        // down to modes, submodes and actions.
        $this->adminMode = $adminMode;
        
        parent::__construct($driver);
        
        $this->uiStarted = false;
    }
    
   /**
    * @return string
    */
    abstract public function getDefaultMode() : string;

    /**
     * @return string
     */
    abstract public function getNavigationGroup() : string;

    abstract public function isUserAllowed() : bool;
    
   /**
    * Retrieves the names of administration areas this 
    * area depends on, and which may not be disabled as
    * a result when this is enabled.
    * 
    * @return string[] The URL names of the areas
    */
    abstract public function getDependencies() : array;
    
   /**
    * Whether this administration area is a core area that
    * cannot be disabled in the application sets.
    * 
    * @return boolean
    */
    abstract public function isCore() : bool;

    public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultMode();
    }
    
    /**
     * Checks whether this page has different modes (sub- administration sections).
     * @return boolean
     */
    public function hasModes() : bool
    {
        return $this->hasSubscreens();
    }

    /**
     * Cached property for the navigation item of the page for the main navigation.
     * @var UI_Page_Navigation_Item|NULL
     * @see addToNavigation()
     */
    protected ?UI_Page_Navigation_Item $navigationItem = null;

    /**
     * Adds the page to the main navigation.
     * @param UI_Page_Navigation $nav
     * @return UI_Page_Navigation_Item|null
     */
    public function addToNavigation(UI_Page_Navigation $nav) : ?UI_Page_Navigation_Item
    {
        if (!$this->isUserAllowed()) {
            return null;
        }

        $urlName = $this->getURLName();

        $this->navigationItem = $nav->addInternalLink(
            $urlName,
            $this->getNavigationTitle()
        );

        $group = $this->getNavigationGroup();
        if (!empty($group)) {
            $this->navigationItem->setGroup($group);
        }

        if ($this->isActive()) {
            $this->navigationItem->setActive();
        }

        $icon = $this->getNavigationIcon();
        if ($icon instanceof UI_Icon) {
            $this->navigationItem->setIcon($icon);
        }

        return $this->navigationItem;
    }

    /**
     * Retrieves the active mode object if any.
     * @return Application_Admin_Area_Mode|NULL
     */
    public function getMode() : ?Application_Admin_Area_Mode
    {
        $screen = $this->getActiveSubscreen();
        
        if($screen instanceof Application_Admin_Area_Mode)
        {
            return $screen;
        }
        
        return null;
    }

    /**
     * Retrieves the active submode object, if any.
     * 
     * @return Application_Admin_Area_Mode_Submode|NULL
     */
    public function getSubmode() : ?Application_Admin_Area_Mode_Submode
    {
        $mode = $this->getMode();
        
        if($mode) 
        {
            return $mode->getSubmode();
        }

        return null;
    }

    /**
     * Retrieves the active action object if any.
     * @return Application_Admin_Area_Mode_Submode_Action|NULL
     */
    public function getAction() : ?Application_Admin_Area_Mode_Submode_Action
    {
        $submode = $this->getSubmode();
        
        if($submode) 
        {
            return $submode->getAction();
        }

        return null;
    }

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
    public function handleUI() : void
    {
        $this->logUI('Starting the UI.');

        $page = $this->requirePage();

        $subnav = $page->createNavigation('subnav');
        $sidebar = $page->getSidebar();
        $help = $page->createHelp();
        $contextMenu = $this->ui->createDropdownMenu();
        $tabs = $this->ui->createTabs('page-content-tabs');

        $this->handleSubnavigation($subnav);
        $this->handleQuickNavigation($page->getHeader());
        $this->handleBreadcrumb();
        $this->handleHelp($help);
        $this->handleContextMenu($contextMenu);
        $this->handleSidebar($sidebar);
        $this->handleTabs($tabs);
        
        $subnav->initDone();
    }
    
    /**
     * Returns an optional icon object which will be used to display
     * the area in the main navigation.
     *
     * @return UI_Icon|NULL
     */
    public function getNavigationIcon() : ?UI_Icon
    {
        return null;
    }
    
   /**
    * Retrieves all areas this area depends on to work.
    * 
    * @return Application_Admin_Area[]
    */
    public function getDependentAreas()
    {
        $ids = $this->getDependencies();
        if(empty($ids)) {
            return array();
        }
        
        $areas = array();
        foreach($ids as $id) {
            $areas[] = $this->driver->createArea($id);
        }
        
        usort($areas, array($this, 'callback_sortAreas'));
        
        return $areas;
    }
    
    public function callback_sortAreas(Application_Admin_Area $a, Application_Admin_Area $b)
    {
        return strnatcasecmp($a->getTitle(), $b->getTitle());
    }

    public function render() : string
    {
        return $this->renderContent();
    }

    /**
     * Allows the area to add items to show in the quick
     * navigation, which is shown whenever the area is
     * active.
     *
     * @param UI_Page_Navigation $nav
     * @return void
     */
    protected function _handleQuickNavigation(UI_Page_Navigation $nav) : void
    {

    }

    private function handleQuickNavigation(UI_Page_Header $header) : void
    {
        $quickNav = $header->getPage()->createNavigation(self::NAV_AREA_QUICK_NAVIGATION);

        $this->_handleQuickNavigation($quickNav);

        if($quickNav->hasItems())
        {
            $this->logUI('Area has quick navigation items.');
            $header->addNavigationInstance($quickNav);
        }
        else
        {
            $this->logUI('Area has no quick navigation items.');
        }
    }
}
