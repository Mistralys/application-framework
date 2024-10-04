<?php
/**
 * File containing the {@see Application_Traits_Admin_Screen} trait.
 * 
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_Screen
 */

use Application\Admin\ScreenEvents;
use Application\Admin\Screens\Events\ActionsHandledEvent;
use Application\Admin\Screens\Events\BeforeActionsHandledEvent;
use Application\Admin\Screens\Events\BeforeBreadcrumbHandledEvent;
use Application\Admin\Screens\Events\BeforeContentRenderedEvent;
use Application\Admin\Screens\Events\BeforeSidebarHandledEvent;
use Application\Admin\Screens\Events\BreadcrumbHandledEvent;
use Application\Admin\Screens\Events\ContentRenderedEvent;
use Application\Admin\Screens\Events\SidebarHandledEvent;
use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\RenderableInterface;
use UI\Page\Navigation\QuickNavigation;

/**
 * Trait used by all administration screens: used to 
 * dispatch the <code>handleXXX</code> methods down the
 * screens chain. 
 * 
 * In practice this means that calling the method <code>handleSubnavigation()</code>
 * will be called initially on the active admin area, but 
 * from there will recurse into all subscreens as applicable:
 * 
 * Area > Mode > Submode > Action
 * 
 * NOTE: If any of the screens in the chain return a boolean false 
 * from a handleXXX method, it will stop there and not recurse
 * into any subscreens that are left. 
 * 
 * Admin screens can choose to overwrite any or all of the 
 * following protected methods:
 * 
 * - _handleActions
 * - _handleBeforeActions
 * - _handleBreadcrumb
 * - _handleContextMenu
 * - _handleTabs
 * - _handleHelp
 * - _handleSidebar
 * - _handleSubnavigation
 * - _handleQuickNavigation
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see Application_Admin_Area
 * @see Application_Admin_Area_Mode
 * @see Application_Admin_Area_Mode_Submode
 * @see Application_Admin_Area_Mode_Submode_Action
 * 
 * @see AdminScreenInterface
 * 
 * @property Application_Driver $driver
 */
trait Application_Traits_Admin_Screen
{
    protected ?string $screenID = null;
    
   /**
    * Cached flag for the active state of the page.
    * @var boolean|NULL
    * @see isActive()
    */
    protected ?bool $active = null;
    
   /**
    * Caches subscreen IDs.
    * @var string[]|NULL
    */
    protected ?array $subscreenIDs = null;
    
    /**
     * Stores subscreen instances that have been loaded.
     * @var AdminScreenInterface[]
     */
    protected array $subscreens = array();
    
   /**
    * Caches the screen's URL param name.
    * @var string|NULL
    */
    protected ?string $urlParam = null;
    
   /**
    * Caches the screen's log prefix.
    * @var string|NULL
    */
    protected ?string $logPrefix = null;
    
   /**
    * Caches the screen's URL path.
    * @var string|NULL
    */
    protected ?string $urlPath = null;
    
    protected ?string $activeSubscreenID = null;

    /**
     * @var array<string,string|NULL>
     */
    private array $subscreenSearches = array();

   /**
    * Caches the screen's parent screens stack.
    * @var AdminScreenInterface[]|NULL
    */
    protected ?array $parentScreens = null;
    
   /**
    * Caches the screen's ID path.
    * @var string|NULL
    * @see Application_Traits_Admin_Screen::getIDPath()
    */
    protected ?string $idPath = null;
    
   /**
    * Caches the name of the subscreen's URL parameter.
    * @var string|NULL
    */
    protected ?string $subscreenURLParam = null;

    protected QuickNavigation $quickNav;
    
   /**
    * Handles any actions that need to be executed before 
    * the UI is rendered, like validating forms and the like.
    * 
    * NOTE: When the UI layer is disabled (running the application
    * in script mode), this will not be executed to avoid 
    * actually trying to handle the request the screen is built for.
    * 
    * @return boolean
    */
    public function handleActions() : bool
    {
        $this->log('Handling actions.');
        
        if(!$this->isAdminMode())
        {
            $this->log('Handling actions | Not in admin mode, ignoring.');
            return false;
        }
        
        if(!$this->isUserAllowed()) 
        {
            $this->log('Handling actions | User is not authorized, ignoring.');
            return false;
        }
        
        $this->log('Handling actions | Executing before actions.');

        $this->_handleBeforeActions();

        $eventBefore = $this->triggerBeforeActionsHandled();
        if($eventBefore !== null && $eventBefore->isCancelled()) {
            $this->log('Handling Actions | Cancelled by the before actions event.');
            return false;
        }

        $this->log('Handling actions | Executing actions.');
        
        if($this->_handleActions() === false)
        {
            return false;
        }

        $this->triggerActionsHandled();

        if(!$this->hasSubscreens()) {
            return true;
        }

        $this->log('Handling actions | Handling sub-screen.');

        $sub = $this->getActiveSubscreen();
        if($sub !== null)
        {
            $this->log('Handling actions | Executing sub-screen actions.');
            $sub->handleActions();
        }

        return true;
    }

    protected function _handleBeforeActions() : void
    {
    }

    // region: Event handling

    public function onBeforeActionsHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            BeforeActionsHandledEvent::EVENT_NAME,
            $listener
        );
    }


    public function onSidebarHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            SidebarHandledEvent::EVENT_NAME,
            $listener
        );
    }

    public function onBeforeSidebarHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            BeforeSidebarHandledEvent::EVENT_NAME,
            $listener
        );
    }

    public function onBreadcrumbHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            BreadcrumbHandledEvent::EVENT_NAME,
            $listener
        );
    }

    public function onBeforeBreadcrumbHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            BeforeBreadcrumbHandledEvent::EVENT_NAME,
            $listener
        );
    }

    public function onActionsHandled(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            ActionsHandledEvent::EVENT_NAME,
            $listener
        );
    }

    /**
     * Listen to when the screen has finished rendering its content.
     *
     * The listener gets one parameter:
     *
     * 1. Instance of {@see ContentRenderedEvent}
     *
     * NOTE: Use {@see ContentRenderedEvent::hasRenderedContent()} to
     * check if the screen had any content to render.
     *
     * @param callable $listener
     * @return Application_EventHandler_EventableListener
     */
    public function onContentRendered(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            ContentRenderedEvent::EVENT_NAME,
            $listener
        );
    }

    /**
     * Listen to when the screen is getting ready to render
     * its content.
     *
     * The listener gets one parameter:
     *
     * 1. Instance of {@see BeforeContentRenderedEvent}
     *
     * This has the possibility to override the screen's
     * content by calling {@see BeforeContentRenderedEvent::replaceScreenContentWith()}.
     *
     * One use case for this is a tie-in class that automatically
     * displays a selection list of items when a request parameter
     * is not present.
     *
     * Imagine a screen that needs a media document to be specified:
     * The tie-in can check the request, and display a list of media
     * documents to choose from if none is specified. This allows the
     * selection code to not be duplicated across all screens that need it.
     *
     * @param callable $listener
     * @return Application_EventHandler_EventableListener
     */
    public function onBeforeContentRendered(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            BeforeContentRenderedEvent::EVENT_NAME,
            $listener
        );
    }

    private function triggerBeforeActionsHandled() : ?BeforeActionsHandledEvent
    {
        return $this->triggerEventClass(
            BeforeActionsHandledEvent::EVENT_NAME,
            BeforeActionsHandledEvent::class,
            array($this)
        );
    }

    private function triggerActionsHandled() : void
    {
        $this->triggerEventClass(
            ActionsHandledEvent::EVENT_NAME,
            ActionsHandledEvent::class,
            array($this)
        );
    }

    private function triggerContentRendered(bool $hasContent) : ?ContentRenderedEvent
    {
        return $this->triggerEventClass(
            ContentRenderedEvent::EVENT_NAME,
            ContentRenderedEvent::class,
            array($this, $hasContent)
        );
    }

    private function triggerBeforeContentRendered() : ?BeforeContentRenderedEvent
    {
        return $this->triggerEventClass(
            BeforeContentRenderedEvent::EVENT_NAME,
            BeforeContentRenderedEvent::class,
            array($this)
        );
    }

    private function triggerBeforeSidebarHandled() : void
    {
        $this->triggerEventClass(
            BeforeSidebarHandledEvent::EVENT_NAME,
            BeforeSidebarHandledEvent::class,
            array($this)
        );
    }

    private function triggerSidebarHandled() : void
    {
        $this->triggerEventClass(
            SidebarHandledEvent::EVENT_NAME,
            SidebarHandledEvent::class,
            array($this)
        );
    }

    private function triggerBreadcrumbHandled() : void
    {
        $this->triggerEventClass(
            BreadcrumbHandledEvent::EVENT_NAME,
            BreadcrumbHandledEvent::class,
            array($this)
        );
    }

    private function triggerBeforeBreadcrumbHandled() : void
    {
        $this->triggerEventClass(
            BeforeBreadcrumbHandledEvent::EVENT_NAME,
            BeforeBreadcrumbHandledEvent::class,
            array($this)
        );
    }

    // endregion

    /**
     * @return bool
     */
    protected function _handleActions() : bool
    {
        return true;
    }
    
   /**
    * Used to call a <code>handleXXX()</code> method dynamically,
    * without a parameter. Automatically checks if the user is
    * allowed to do so, and recurses into subscreens if available.
    * 
    * @param string $publicMethod
    * @return bool
    */
    protected function _handleUIMethod(string $publicMethod) : bool
    {
        $protectedMethod = '_'.$publicMethod;
        
        if(!$this->isUserAllowed()) 
        {
            return false;
        }
        
        $sub = $this->getActiveSubscreen();
        
        if($this->$protectedMethod() === false)
        {
            return false;
        }
        
        if($sub !== null)
        {
            $sub->$publicMethod();
        }
        
        return true;
    }
    
   /**
    * Like <code>_handleUIMethod()</code>, but with an object instance as
    * parameter that will be stored in an internal property and passed
    * on to any subscreens.
    * 
    * @param string $publicMethod The name of the method to call. e.g. "Subnavigation".
    * @param object $subject The object to store and pass on.
    * @return bool
    */
    protected function _handleUIMethodObject(string $publicMethod, object $subject) : bool
    {
        $protectedMethod = '_'.$publicMethod;

        $this->logUI('Handling '.$publicMethod);
        
        if(!$this->isUserAllowed()) 
        {
            return false;
        }
        
        if($this->$protectedMethod() === false) 
        {
            return false;
        } 
        
        $sub = $this->getActiveSubscreen();
        
        if($sub !== null) 
        {
            $sub->$publicMethod($subject);
        }
        else if($subject instanceof Application_LockableItem_Interface && $this->isLocked())
        {
            $subject->lock($this->requireLockManager()->getLockReason());
        }
        
        return true;
    }
    
   /**
    * Handles configuring the breadcrumb for the screen.
    * 
    * The breadcrumb object is always present when the UI layer
    * is enabled, so it does not have to be passed to this method.
    * 
    * @see Application_Admin_Skeleton::startUI()
    */
    public function handleBreadcrumb() : void
    {
        $this->triggerBeforeBreadcrumbHandled();

        $this->_handleUIMethod(array($this, 'handleBreadcrumb')[1]);

        $this->triggerBreadcrumbHandled();
    }

    /**
     * @return void
     */
    protected function  _handleBreadcrumb() : void
    {
        
    }

    /**
     * @param UI_Page_Sidebar $sidebar
     * @return void
     * @see Application_Traits_Admin_Screen::_handleSidebar()
     */
    public function handleSidebar(UI_Page_Sidebar $sidebar) : void
    {
        $this->sidebar = $sidebar;

        $this->triggerBeforeSidebarHandled();

        $this->_handleUIMethodObject(array($this, 'handleSidebar')[1], $sidebar);

        $this->triggerSidebarHandled();
    }

    protected function _handleSidebar() : void
    {
    }

    /**
     * @param UI_Page_Navigation $subnav
     * @return void
     * @see Application_Traits_Admin_Screen::_handleSubnavigation()
     */
    public function handleSubnavigation(UI_Page_Navigation $subnav) : void
    {
        $this->subnav = $subnav;

        $this->_handleUIMethodObject(array($this, 'handleSubnavigation')[1], $subnav);
    }

    protected function _handleSubnavigation() : void
    {
        
    }

    /**
     * @param UI_Bootstrap_DropdownMenu $menu
     * @return void
     * @see Application_Traits_Admin_Screen::_handleContextMenu()
     */
    public function handleContextMenu(UI_Bootstrap_DropdownMenu $menu) : void
    {
        $this->contextmenu = $menu;

        $this->_handleUIMethodObject(array($this, 'handleContextMenu')[1], $menu);
    }

    protected function _handleContextMenu() : void
    {
        
    }

    /**
     * @param UI_Bootstrap_Tabs $tabs
     * @return void
     * @see Application_Traits_Admin_Screen::_handleTabs()
     */
    public function handleTabs(UI_Bootstrap_Tabs $tabs) : void
    {
        $this->tabs = $tabs;

        $this->_handleUIMethodObject(array($this, 'handleTabs')[1], $tabs);
    }

    protected function _handleTabs() : void
    {

    }

    /**
     * @param QuickNavigation $navigation
     * @return void
     * @see Application_Traits_Admin_Screen::_handleQuickNavigation()
     */
    public function handleQuickNavigation(QuickNavigation $navigation) : void
    {
        $navigation->setWorkScreen($this);

        $this->quickNav = $navigation;

        $this->_handleUIMethodObject(array($this, 'handleQuickNavigation')[1], $navigation);
    }

    protected function _handleQuickNavigation() : void
    {

    }

    /**
     * @param UI_Page_Help $help
     * @return void
     * @see Application_Traits_Admin_Screen::_handleHelp()
     */
    public function handleHelp(UI_Page_Help $help) : void
    {
        $this->help = $help;

        $this->_handleUIMethodObject(array($this, 'handleHelp')[1], $help);
    }

    protected function _handleHelp() : void
    {
        
    }

    /**
     * @return string
     * @see Application_Traits_Admin_Screen::_renderContent()
     */
    public function renderContent() : string
    {
        $this->log('Render content');
        
        if(!$this->isUserAllowed())
        {
            $this->log('Render content | User not authorized.');
            return $this->renderUnauthorized();
        }

        $content = $this->renderOwnContent();
        $hasOwnContent = !empty($content);

        $this->triggerContentRendered($hasOwnContent);

        if($hasOwnContent) {
            return $content;
        }

        $subScreen = $this->getActiveSubscreen();

        if($subScreen)
        {
            $this->log('Render content | Rending sub-screen content.');
            return $subScreen->renderContent();
        }

        return '';
    }

    /**
     * Determines the rendered content for this screen,
     * excluding its sub-screens.
     *
     * @return string
     */
    private function renderOwnContent() : string
    {
        $event = $this->triggerBeforeContentRendered();

        if($event !== null && $event->replacesContent()) {
            return $event->getContent();
        }

        return (string)$this->_renderContent();
    }

    /**
     * @return string|RenderableInterface|UI_Themes_Theme_ContentRenderer|NULL Return NULL or an empty string if the screen has nothing to display.
     */
    protected function _renderContent()
    {
        return null;
    }

   /**
    * Retrieves the tabs instance, if any.
    * 
    * @return UI_Bootstrap_Tabs|NULL
    */
    public function getTabs() : ?UI_Bootstrap_Tabs
    {
        return $this->tabs;
    }
    
    public function hasTabs() : bool
    {
        return isset($this->tabs);
    }
    
    /**
     * @return UI_Page_Help|NULL
     */
    public function getHelp() : ?UI_Page_Help
    {
        return $this->help;
    }
    
    /**
     * Retrieves the instance of the sidebar, if any.
     * @return UI_Page_Sidebar|NULL
     */
    public function getSidebar() : ?UI_Page_Sidebar
    {
        return $this->sidebar;
    }

    /**
     * @return UI_Page_Sidebar
     * @throws Application_Admin_Exception
     */
    public function requireSidebar() : UI_Page_Sidebar
    {
        if(isset($this->sidebar))
        {
            return $this->sidebar;
        }

        throw new Application_Admin_Exception(
            'No sidebar available at this time.',
            '',
            AdminScreenInterface::ERROR_SIDEBAR_NOT_AVAILABLE_YET
        );
    }

    public function isArea() : bool
    {
        return $this instanceof Application_Admin_Area;
    }
    
    public function isMode() : bool
    {
        return $this instanceof Application_Admin_Area_Mode;
    }

    public function isSubmode() : bool
    {
        return $this instanceof Application_Admin_Area_Mode_Submode;
    }
    
    public function isAction() : bool
    {
        return $this instanceof Application_Admin_Area_Mode_Submode_Action;
    }

    /**
     * Retrieves the screen's admin area instance, if any.
     * @return Application_Admin_Area
     * @throws Application_Admin_Exception
     */
    public function getArea() : Application_Admin_Area
    {
        if($this instanceof Application_Admin_Area) 
        {
            return $this;
        }
        
        $parent = $this->getParentScreen();
        
        if($parent)
        {
            return $parent->getArea();
        }
        
        throw new Application_Admin_Exception(
            'Administration screen has no area.',
            'Path to screen: '.$this->getURLPath(),
            AdminScreenInterface::ERROR_SCREEN_HAS_NO_AREA
        );
    }
    
   /**
    * Whether this screen has an active subscreen. 
    * 
    * @return bool
    */
    public function hasActiveSubscreen() : bool
    {
        return $this->getActiveSubscreen() !== null;
    }
    
   /**
    * Retrieves all parent screens up to (but not including)
    * this screen, with the area at the top. If this is the 
    * area, the array will have only the area.
    * 
    * @return AdminScreenInterface[]
    */
    public function getParentScreens()
    {
        if(isset($this->parentScreens))
        {
            return $this->parentScreens;
        }
        
        $stack = array();
        $screen = $this->getParentScreen();
        
        if($screen === null)
        {
            return $stack;
        }
        
        array_unshift($stack, $screen);
        
        while(!$screen instanceof Application_Admin_Area)
        {
            $screen = $screen->getParentScreen();
            
            array_unshift($stack, $screen);
        }
        
        $this->parentScreens = $stack;
        
        return $this->parentScreens;
    }

    public function getID() : string
    {
        if (!isset($this->screenID)) 
        {
            $this->screenID = getClassTypeName($this);
        }
        
        return $this->screenID;
    }
    
   /**
    * Retrieves an URL to this screen.
    * 
    * @param array $params
    */
    public function getURL(array $params = array()) : string
    {
        $screens = $this->getParentScreens();
        
        foreach($screens as $screen)
        {
            $params[$screen->getURLParam()] = $screen->getURLName();
        }
        
        $params[$this->getURLParam()] = $this->getURLName();
        
        return $this->request->buildURL($params);
    }
    
   /**
    * Retrieves the URL path to the screen, in the
    * format <code>area.mode.submode.action</code>.
    * 
    * @return string
    */
    public function getURLPath() : string
    {
        if(isset($this->urlPath))
        {
            return $this->urlPath;
        }

        $parts = array();
        $screens = $this->getParentScreens();
        
        foreach($screens as $screen)
        {
            $parts[] = $screen->getURLName();
        }
        
        $parts[] = $this->getURLName();
        
        $this->urlPath = implode('.', $parts);
        
        return $this->urlPath;
    }

    protected function getIDPath() : string
    {
        if(isset($this->idPath))
        {
            return $this->idPath;
        }
        
        $parts = array();
        $screens = $this->getParentScreens();
        
        foreach($screens as $screen)
        {
            $parts[] = $screen->getID();
        }
        
        $parts[] = $this->getID();
        
        $this->idPath = implode('_', $parts);
        
        return $this->idPath;
    }
    
    public function getURLParam() : string
    {
        if(!isset($this->urlParam))
        {
            $this->urlParam = $this->driver->resolveURLParam($this);
        }
        
        return $this->urlParam;
    }
    
    public function isActive() : bool
    {
        if(isset($this->active)) 
        {
            return $this->active;
        }
        
        $this->active = false;
        
        $mine = $this->getURLPath();
        $current = $this->driver->getActiveScreen()->getURLPath();
        
        $this->active = substr($current, 0, strlen($mine)) === $mine;
        
        return $this->active;
    }

   /**
    * Retrieves the currently active administration screen.
    * 
    * @return AdminScreenInterface
    */
    public function getActiveScreen() : AdminScreenInterface
    {
        $target = $this->getArea();
        
        while($target->hasActiveSubscreen())
        {
            $target = $target->getActiveSubscreen();
        }
        
        return $target;
    }


    /**
     * Retrieves a list of IDs of all subscreens available for the screen, if any.
     *
     * NOTE: Does not check if the file contains a valid subscreen class.
     *
     * @return array<string,string> Class ID => URL name pairs
     *
     * @throws FileHelper_Exception
     * @throws UI_Exception
     */
    public function getSubscreenIDs() : array
    {
        if(isset($this->subscreenIDs))
        {
            return $this->subscreenIDs;
        }
        
        $this->subscreenIDs = array();
        
        $folder = $this->getSubscreensFolder();

        if(!is_dir($folder))
        {
            return $this->subscreenIDs;
        }

        $ids = FileHelper::createFileFinder($folder)
            ->getPHPClassNames();

        foreach($ids as $id)
        {
            try
            {
                $screen = $this->createSubscreenInstance($id, false);
                $this->subscreenIDs[$id] = $screen->getURLName();
            }
            catch (Throwable $e)
            {
                $this->getLogger()->logUI(
                    'Cannot create screen instance: [%s.%s]. Error: [%s]',
                    $this->getURLPath(),
                    $id,
                    $e->getMessage()
                );

                throw new UI_Exception(
                    'Cannot instantiate admin screen.',
                    'An exception occurred when creating the screen.',
                    AdminScreenInterface::ERROR_CANNOT_INSTANTIATE_SCREEN,
                    $e
                );
            }
        }
        
        return $this->subscreenIDs;
    }
    
   /**
    * Retrieves the path to the screen's subscreens folder.
    * 
    * @return string
    */
    public function getSubscreensFolder() : string
    {
        return sprintf(
            '%s/assets/classes/%s/Area/%s',
            APP_ROOT,
            $this->driver->getID(),
            str_replace('_', '/', $this->getIDPath())
        );
    }
    
   /**
    * Whether the screen has any subscreens.
    * 
    * @return bool
    */
    public function hasSubscreens() : bool
    {
        $ids = $this->getSubscreenIDs();
        
        return !empty($ids);
    }
    
    public function hasSubscreen(string $id) : bool
    {
        $screenID = $this->resolveSubscreenID($id);
        
        return !empty($screenID);
    }
    
    public function getSubscreenByID(string $id, bool $adminMode) : AdminScreenInterface
    {
        return $this->createSubscreen($id, $adminMode);
    }

    /**
     * @param string $id
     * @param bool $adminMode
     * @return AdminScreenInterface
     * @throws Application_Exception
     */
    protected function createSubscreen(string $id, bool $adminMode) : AdminScreenInterface
    {
        $screenID = $this->requireValidSubscreenID($id);
        $key = $screenID.'.'.ConvertHelper::boolStrict2string($adminMode);
        
        if(isset($this->subscreens[$key]))
        {
            return $this->subscreens[$key];
        }

        $this->log(sprintf('Creating child screen [%s] with class ID [%s].', $id, $screenID));

        $screen = $this->createSubscreenInstance($screenID, $adminMode);
        $this->subscreens[$key] = $screen;

        return $screen;
    }

    protected function createSubscreenInstance(string $screenID, bool $adminMode) : AdminScreenInterface
    {
        $class = ClassHelper::requireResolvedClass(sprintf(
            '%s_%s',
            get_class($this),
            $screenID
        ));

        $previousMode = $this->adminMode;

        if(!$adminMode && $this->adminMode) {
            $this->adminMode = false;
        }

        $instance = ClassHelper::requireObjectInstanceOf(
            AdminScreenInterface::class,
            new $class($this->driver, $this)
        );

        $this->log(
            'Created child screen with class ID [%s] and URL name [%s].',
            $screenID,
            $instance->getURLName()
        );

        $this->adminMode = $previousMode;

        return $instance;
    }

    /**
     * Given the ID of a subscreen of this administration screen,
     * returns the case-sensitive ID of the class of the subscreen.
     *
     * Example: Admin area "Products" requires child screen by
     * id "editproduct", which is its URL name. The class name
     * however, can have any case in its name, e.g. "EditProduct".
     * This will return the matching case-sensitive screen ID by
     * finding the appropriate file in the filesystem.
     *
     * This presupposes that the case of the filename matches the
     * case in the class name.
     *
     * @param string $id
     * @throws Application_Exception
     * @return string
     */
    protected function requireValidSubscreenID(string $id) : string
    {
        $screenID = $this->resolveSubscreenID($id);
        
        if(!empty($screenID))
        {
            return $screenID;
        }
        
        throw new Application_Exception(
            'No such child administration screen.',
            sprintf(
                'The administration screen [%s] has no child screen [%s]. Available child screens are [%s]. Looking in URL parameter [%s].',
                get_class($this),
                $id,
                implode(', ', array_keys($this->getSubscreenIDs())),
                $this->getURLParam()
            ),
            Application_Admin_Skeleton::ERROR_NO_SUCH_CHILD_ADMIN_SCREEN
        );
    }

    protected function resolveSubscreenID(string $search) : ?string
    {
        if(array_key_exists($search, $this->subscreenSearches)) {
            return $this->subscreenSearches[$search];
        }

        $this->subscreenSearches[$search] = null;

        $ids = $this->getSubscreenIDs();

        $compare = strtolower($search);

        foreach($ids as $subscreenID => $urlName)
        {
            $sub = strtolower($subscreenID);

            // The search term can be either the screen ID
            // or a URL name, which is why we must check both.
            // The screen ID takes precedence, as it is more
            // precise and guaranteed to be unique.
            if($compare === $sub || $compare === $urlName)
            {
                $this->subscreenSearches[$search] = $subscreenID;
                return $subscreenID;
            }
        }

        return null;
    }
    
   /**
    * Resolves and returns the ID of the active subscreen, if any.
    * 
    * @return string The subscreen ID, or an empty string otherwise.
    */
    public function getActiveSubscreenID() : string
    {
        if(isset($this->activeSubscreenID))
        {
            return $this->activeSubscreenID;
        }
        
        $this->activeSubscreenID = '';
        
        $paramName = $this->getSubscreenURLParam();
        
        if(!empty($paramName))
        {
            $this->activeSubscreenID = (string)$this->request->registerParam($paramName)
            ->get($this->getDefaultSubscreenID());
        }

        return $this->activeSubscreenID;
    }
    
   /**
    * Determines the name of the URL parameter of this
    * screen's subscreen (if it even has any).
    * 
    * @throws Application_Exception
    * @return string The param name, or an empty string otherwise.
    */
    protected function getSubscreenURLParam() : string
    {
        if(isset($this->subscreenURLParam))
        {
            return $this->subscreenURLParam;
        }
        
        $this->subscreenURLParam = '';
        
        if(!$this->hasSubscreens())
        {
            return $this->subscreenURLParam;
        }
        
        $names = $this->driver->getURLParamNames();
        
        $pos = array_search($this->getURLParam(), $names, true);
        
        if($pos === false)
        {
            throw new Application_Exception(
                'Screen URL parameter not present.',
                sprintf(
                    'The URL parameter [%s] of screen [%s] was not found in the driver\'s URL parameters list: [%s].',
                    $this->getURLParam(),
                    get_class($this),
                    implode(', ', $names)
                ),
                AdminScreenInterface::ERROR_MISSING_URL_PARAMETER
            );
        }
        
        $next = $pos + 1;
        
        if(isset($names[$next]))
        {
            $this->subscreenURLParam = $names[$next];
        }
        
        return $this->subscreenURLParam;
    }
    
    public function getDefaultSubscreenID() : string
    {
        return '';
    }
    
    public function getActiveSubscreen() : ?AdminScreenInterface
    {
        $id = $this->getActiveSubscreenID();
        
        if(!empty($id))
        {
            return $this->createSubscreen($id, $this->adminMode);
        }
        
        return null;
    }

    public function getParentScreen() : ?AdminScreenInterface
    {
        return $this->parentScreen;
    }
    
   /**
    * Initializes the screen, when in admin mode. When not in admin
    * mode, the initialization is ignored. This is called in the 
    * constructor of the screens types.
    */
    protected function initScreen() : void
    {
        if(!$this->isAdminMode())
        {
            return;
        }

        $this->request->setParam($this->getURLParam(), $this->getURLName());
        $this->init();
    }

   /**
    * Can be extended by the screen for any required internal
    * initializations.
    */
    protected function init() : void
    {
        
    }

    public function getLogIdentifier() : string
    {
        if(isset($this->logPrefix)) {
            return $this->logPrefix;
        }

        $type = 'Screen';

        if($this->isAdminMode())
        {
            $type = 'Admin Screen';
        }

        $this->logPrefix = sprintf(
            '%s [%s] [%s]',
            $type,
            $this->getURLPath(),
            $this->getInstanceID()
        );

        return $this->logPrefix;
    }
}
