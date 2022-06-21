<?php
/**
 * File containing the {@see Application_Admin_ScreenInterface} interface.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_ScreenInterface
 */

/**
 * Interface for administration screens: defines all methods
 * that administration screens have to share beyond what the
 * skeleton offers.
 * 
 * NOTE: This is mostly implemented in the matching trait.
 * 
 * WARNING: The interface is not type hinted for backwards
 * compatibility.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Screen
 * @see Application_Admin_Skeleton
 * @see Application_Interfaces_Formable
 */
interface Application_Admin_ScreenInterface extends Application_Interfaces_Formable, Application_Interfaces_Loggable
{
    public const ERROR_SIDEBAR_NOT_AVAILABLE_YET = 96701;
    public const ERROR_MISSING_URL_PARAMETER = 96702;
    public const ERROR_SCREEN_HAS_NO_AREA = 96703;

    public const REQUEST_PARAM_PAGE = 'page';
    public const REQUEST_PARAM_MODE = 'mode';
    public const REQUEST_PARAM_SUBMODE = 'submode';
    public const REQUEST_PARAM_ACTION = 'action';

    /**
    * @return string
    */
    public function getID() : string;
    
   /**
    * @return UI 
    */
    public function getUI() : UI;
    
   /**
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function getRenderer() : UI_Themes_Theme_ContentRenderer;
    
   /**
    * Handles any actions to execute before the UI layer is started. 
    */
    public function handleActions() : bool;
    
   /**
    * Allows configuring the breadcrumb instance for the current page.
    */
    public function handleBreadcrumb() : void;
    
   /**
    * Allows configuring the help instance for the current page.
    * 
    * @param UI_Page_Help $help
    */
    public function handleHelp(UI_Page_Help $help) : void;
    
   /**
    * Allows configuring the sidebar instance for the current page.
    * 
    * @param UI_Page_Sidebar $sidebar
    */
    public function handleSidebar(UI_Page_Sidebar $sidebar) : void;
    
   /**
    * Allows configuring the subnavigation for the current page.
    * 
    * @param UI_Page_Navigation $subnav
    */
    public function handleSubnavigation(UI_Page_Navigation $subnav) : void;
    
   /**
    * Allows configuring the context menu in the subnavigation for the current page.
    * 
    * @param UI_Bootstrap_DropdownMenu $menu
    */
    public function handleContextMenu(UI_Bootstrap_DropdownMenu $menu) : void;
    
   /**
    * Allows configuring the content tabs for the current page.
    *
    * @param UI_Bootstrap_Tabs $tabs
    */
    public function handleTabs(UI_Bootstrap_Tabs $tabs) : void;
    
   /**
    * Whether the current user is allowed to access this screen / feature.
    * 
    * @return bool
    */
    public function isUserAllowed() : bool;
    
   /**
    * Whether the screens are running in admin mode, and actions may be executed.
    * 
    * @return bool
    * @see Application_Admin_Skeleton::isAdminMode()
    */
    public function isAdminMode() : bool;
    
   /**
    * Retrieves the current screen's subscreen, if any.
    * 
    * @return Application_Admin_ScreenInterface|NULL
    */
    public function getActiveSubscreen() : ?Application_Admin_ScreenInterface;
    
   /**
    * Whether the screen has an active subscreen.
    * 
    * @return bool
    */
    public function hasActiveSubscreen() : bool;
    
   /**
    * @return string
    */
    public function getURLName() : string;
    
   /**
    * @return string
    */
    public function getURLPath() : string;
    
   /**
    * Retrieves the name of the parameter used to select this screen
    * via the request.
    * 
    * @return string
    */
    public function getURLParam() : string;
    
    public function startTransaction() : void;
    
    public function endTransaction() : void;
    
   /**
    * @param string[] $params
    * @return string
    */
    public function getURL(array $params = array()) : string;
    
   /**
    * @return string
    */
    public function getNavigationTitle() : string;
    
   /**
    * @return string
    */
    public function getTitle() : string;
    
   /**
    * Whether this class is an administration area class.
    * @return boolean
    * @see Application_Admin_Area
    */
    public function isArea() : bool;
    
   /**
    * Whether this class is an administration mode class.
    * @return boolean
    * @see Application_Admin_Area_Mode
    */
    public function isMode() : bool;
    
   /**
    * Whether this class is an administration submode class.
    * @return boolean
    * @see Application_Admin_Area_Mode_Submode
    */
    public function isSubmode() : bool;
    
   /**
    * Whether this class is an administration action class.
    * @return boolean
    * @see Application_Admin_Area_Mode_Submode_Action
    */
    public function isAction() : bool;
    
   /**
    * Retrieves the area this screen is a child of.
    * 
    * @return Application_Admin_Area
    */
    public function getArea() : Application_Admin_Area;
    
   /**
    * Retrieves this screen's parent screen: only the 
    * areas have no parent. 
    * 
    * @return Application_Admin_ScreenInterface
    */
    public function getParentScreen() : ?Application_Admin_ScreenInterface;

    public function getSidebar() : ?UI_Page_Sidebar;

    public function requireSidebar() : UI_Page_Sidebar;

    /**
     * Checks whether this screen is currently active (it is
     * in the chain of active screens).
     * 
     * @return boolean
     */
    public function isActive() : bool;
    
   /**
    * Retrieves the currently active administration screen.
    * 
    * @return Application_Admin_ScreenInterface
    */
    public function getActiveScreen() : Application_Admin_ScreenInterface;
    
   /**
    * Retrieves a list of IDs of all subscreens available for the screen, if any.
    * 
    * @return string[]
    */
    public function getSubscreenIDs() : array;
    
   /**
    * Whether the screen has any subscreens.
    * 
    * @return bool
    */
    public function hasSubscreens() : bool;
    
    public function hasSubscreen(string $id) : bool;
    
    public function getSubscreenByID(string $id, bool $adminMode) : Application_Admin_ScreenInterface;
    
   /**
    * Retrieves the ID of the active subscreen, if any.
    * @return string|NULL
    */
    public function getActiveSubscreenID() : ?string;
    
   /**
    * Retrieves the default subscreen ID, if any.
    * 
    * @return string
    */
    public function getDefaultSubscreenID() : string;
    
    public function renderContent() : string;
    
   /**
    * Renders the content of the page's "Help" section.
    * @return string
    */
    public function renderHelp() : string;
    
   /**
    * Adds an error message, and redirects to the target URL.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    * @return never
    */
    public function redirectWithErrorMessage($message, $paramsOrURL) : void;

   /**
    * Adds an error message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    * @return never
    */
    public function redirectWithSuccessMessage($message, $paramsOrURL) : void;
    
   /**
    * Adds an informational message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    * @return never
    */
    public function redirectWithInfoMessage($message, $paramsOrURL) : void;

    /**
     * @param string|array<string,string|int|float> $paramsOrURL
     * @return never
     */
    public function redirectTo($paramsOrURL) : void;

    /**
     * Retrieves all app internal parameters for the screen (page, mode, submode, action).
     *
     * @return array<string,string>
     */
    public function getPageParams() : array;
}
