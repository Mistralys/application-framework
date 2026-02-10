<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

use Application\Admin\Area\Events\UIHandlingCompleteEvent;
use Application\Admin\BaseArea;
use Application\EventHandler\EventManager;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;
use UI\Page\Navigation\QuickNavigation;

/**
 * @package Application
 * @subpackage Administration
 * @deprecated Use {@see BaseArea} instead.
 */
abstract class Application_Admin_Area extends Application_Admin_Skeleton implements AdminAreaInterface
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

    final public function getParentScreenClass(): null
    {
        return null;
    }
    
    final public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultMode();
    }
    
    final public function hasModes() : bool
    {
        return $this->hasSubscreens();
    }

    /**
     * Cached property for the navigation item of the page for the main navigation.
     * @var UI_Page_Navigation_Item|NULL
     * @see addToNavigation()
     */
    protected ?UI_Page_Navigation_Item $navigationItem = null;

    final public function addToNavigation(UI_Page_Navigation $nav) : ?UI_Page_Navigation_Item
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

    final public function getMode() : ?AdminModeInterface
    {
        $screen = $this->getActiveSubscreen();
        
        if($screen instanceof AdminModeInterface)
        {
            return $screen;
        }
        
        return null;
    }

    final public function getSubmode() : ?AdminSubmodeInterface
    {
        return $this->getMode()?->getSubmode();
    }

    final public function getAction() : ?AdminActionInterface
    {
        return $this->getSubmode()?->getAction();
    }

    final public function handleUI() : void
    {
        $this->logUI('Starting the UI.');

        $page = $this->requirePage();
        $subNav = $page->createNavigation('subnav');

        $this->handleSubnavigation($subNav);
        $this->handleBreadcrumb();
        $this->handleHelp($page->createHelp());
        $this->handleContextMenu($this->ui->createDropdownMenu());
        $this->handleSidebar($page->getSidebar());
        $this->handleTabs($this->ui->createTabs('page-content-tabs'));
        $this->handleQuickNavigation($this->createQuickNav());

        EventManager::trigger(
            UIHandlingCompleteEvent::EVENT_NAME,
            array($this),
            UIHandlingCompleteEvent::class
        );

        $subNav->initDone();
    }
    
    public function getNavigationIcon() : ?UI_Icon
    {
        return null;
    }
    
    final public function getDependentAreas() : array
    {
        $ids = $this->getDependencies();
        if(empty($ids)) {
            return array();
        }
        
        $areas = array();
        foreach($ids as $id) {
            $areas[] = $this->driver->createArea($id);
        }
        
        usort($areas, static function(AdminAreaInterface $a, AdminAreaInterface $b): int
        {
            return strnatcasecmp($a->getTitle(), $b->getTitle());
        });
        
        return $areas;
    }
    
    public function render() : string
    {
        return $this->renderContent();
    }

    private function createQuickNav() : QuickNavigation
    {
        return new QuickNavigation($this->getPage()->getHeader());
    }
}
