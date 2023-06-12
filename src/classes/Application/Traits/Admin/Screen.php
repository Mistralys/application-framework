<?php
/**
 * File containing the {@see Application_Traits_Admin_Screen} trait.
 * 
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_Screen
 */

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
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
 * @see Application_Admin_ScreenInterface
 * 
 * @property Application_Driver $driver
 * @property UI_Page_Navigation $subnav
 * @property UI_Page_Help $help
 * @property UI_Page_Sidebar $sidebar
 * @property UI_Bootstrap_Tabs $tabs
 * @property Application_Request $request
 * @property Application_Admin_ScreenInterface|NULL $parentScreen
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
    protected $subscreenIDs;
    
    /**
     * Stores subscreen instances that have been loaded.
     * @var Application_Admin_ScreenInterface[]
     */
    protected $subscreens = array();
    
   /**
    * Caches the screen's URL param name.
    * @var string|NULL
    */
    protected $urlParam;
    
   /**
    * Caches the screen's log prefix.
    * @var string|NULL
    */
    protected ?string $logPrefix = null;
    
   /**
    * Caches the screen's URL path.
    * @var string|NULL
    */
    protected $urlPath;
    
    protected ?string $activeSubscreenID = null;

    /**
     * @var array<string,string|NULL>
     */
    private array $subscreenSearches = array();

   /**
    * Caches the screen's parent screens stack.
    * @var Application_Admin_ScreenInterface[]|NULL
    */
    protected $parentScreens;
    
   /**
    * Caches the screen's ID path.
    * @var string|NULL
    * @see Application_Traits_Admin_Screen::getIDPath()
    */
    protected $idPath;
    
   /**
    * Caches the name of the subscreen's URL parameter.
    * @var string|NULL
    */
    protected $subscreenURLParam;

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
        
        $this->log('Handling actions | Executing actions.');
        
        if($this->_handleActions() === false)
        {
            return false;
        }

        if($this->hasSubscreens())
        {
            $this->log('Handling actions | Handling subscreen.');

            $sub = $this->getActiveSubscreen();
            if($sub !== null)
            {
                $this->log('Handling actions | Executing subscreen actions.');
                $sub->handleActions();
            }
        }
        
        return true;
    }

    protected function _handleBeforeActions() : void
    {
    }

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
    * @param string $name
    * @return bool
    */
    protected function _handleUIMethod(string $name) : bool
    {
        $publicMethod = 'handle'.$name;
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
    * @param string $name The name of the method to call. e.g. "Subnavigation".
    * @param string $property The name of the property to store the object in.
    * @param object $subject The object to store and pass on.
    * @return bool
    */
    protected function _handleUIMethodObject(string $name, string $property, object $subject) : bool
    {
        $publicMethod = 'handle'.$name;
        $protectedMethod = '_'.$publicMethod;

        $this->$property = $subject;
        
        $this->logUI('Handling '.$name);
        
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
        $this->_handleUIMethod('Breadcrumb');
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
        $this->_handleUIMethodObject('Sidebar', 'sidebar', $sidebar);
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
        $this->_handleUIMethodObject('Subnavigation', 'subnav', $subnav);
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
        $this->_handleUIMethodObject('ContextMenu', 'contextmenu', $menu);
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
        $this->_handleUIMethodObject('Tabs', 'tabs', $tabs);
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

        $this->_handleUIMethodObject('QuickNavigation', 'quickNav', $navigation);
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
        $this->_handleUIMethodObject('Help', 'help', $help);
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
        
        $content = $this->_renderContent();
        if(!empty($content))
        {
            $this->log('Render content | Using the area\'s own content.');
            return $content;
        }
        
        if($this->hasSubscreens())
        {
            $subscreen = $this->getActiveSubscreen();
            
            if($subscreen)
            {
                $this->log('Render content | Rending subscreen content.');
                return $subscreen->renderContent();
            }
        }
        {
            $this->log('RenderContent | No subscreens present.');
        }
        
        $this->log('Render content | No content has been rendered.');
        return '';
    }

    /**
     * @return string|UI_Renderable_Interface
     */
    protected function _renderContent()
    {
        return '';
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
            Application_Admin_ScreenInterface::ERROR_SIDEBAR_NOT_AVAILABLE_YET
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
            Application_Admin_ScreenInterface::ERROR_SCREEN_HAS_NO_AREA
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
    * @return Application_Admin_ScreenInterface[]
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
    * @return Application_Admin_ScreenInterface
    */
    public function getActiveScreen() : Application_Admin_ScreenInterface
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
                    Application_Admin_ScreenInterface::ERROR_CANNOT_INSTANTIATE_SCREEN,
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
    
    public function getSubscreenByID(string $id, bool $adminMode) : Application_Admin_ScreenInterface
    {
        return $this->createSubscreen($id, $adminMode);
    }

    /**
     * @param string $id
     * @param bool $adminMode
     * @return Application_Admin_ScreenInterface
     * @throws Application_Exception
     */
    protected function createSubscreen(string $id, bool $adminMode) : Application_Admin_ScreenInterface
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

    protected function createSubscreenInstance(string $screenID, bool $adminMode) : Application_Admin_ScreenInterface
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
            Application_Admin_ScreenInterface::class,
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
                Application_Admin_ScreenInterface::ERROR_MISSING_URL_PARAMETER
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
    
    public function getActiveSubscreen() : ?Application_Admin_ScreenInterface
    {
        $id = $this->getActiveSubscreenID();
        
        if(!empty($id))
        {
            return $this->createSubscreen($id, $this->adminMode);
        }
        
        return null;
    }

    public function getParentScreen() : ?Application_Admin_ScreenInterface
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
