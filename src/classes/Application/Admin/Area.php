<?php

abstract class Application_Admin_Area extends Application_Admin_Skeleton
{
    use Application_Traits_Admin_Screen;
    
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
     * @var UI_Page_Navigation_Item
     * @see addToNavigation()
     */
    protected $navigationItem;

    /**
     * Adds the page to the main navigation.
     * @param UI_Page_Navigation $nav
     */
    public function addToNavigation(UI_Page_Navigation $nav)
    {
        if (!$this->isUserAllowed()) {
            return;
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
    * Creates a mode object instance, or returns an existing instance.
    * 
    * @param string $id
    * @return Application_Admin_Area_Mode
    */
    public function createMode(string $id) : Application_Admin_Area_Mode
    {
        return ensureType(Application_Admin_Area_Mode::class, $this->createSubscreen($id)); 
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
    public function handleUI()
    {
        $this->log('UI layer | Starting the UI.');
        
        $subnav = $this->page->createNavigation('subnav');
        $sidebar = $this->page->getSidebar();
        $help = $this->page->createHelp();
        $contextMenu = $this->ui->createDropdownMenu();
        $tabs = $this->ui->createTabs('page-content-tabs');
        
        $this->handleSidebar($sidebar);
        $this->handleSubnavigation($subnav);
        $this->handleBreadcrumb();
        $this->handleHelp($help);
        $this->handleContextMenu($contextMenu);
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
}
