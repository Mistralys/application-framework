<?php
/**
 * File containing the {@link Application_Driver_Interface} interface.
 * 
 * @package Application
 * @subpackage Interfaces
 * @see Application_Driver_Interface
 */

use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * Interface for driver classes.
 * 
 * @package Application
 * @subpackage Interfaces
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Driver
 */
interface Application_Driver_Interface extends Application_Interfaces_Loggable
{
    public function start() : void;
    
    public function renderContent() : string;

    public function getID() : string;

    /**
     * @param UI_Page $page
     * @return array<string,string|number>
     */
    public function getPageParams(UI_Page $page) : array;
    
    public function getPageID() : string;
    
    public function getRequest() : Application_Request;

    public function getAppName() : string;

    public function getAppNameShort() : string;

    public function getTheme() : UI_Themes_Theme;

    public function getRootFolder() : string;
    public function getClassesFolder() : string;

    public function getConfigFolder() : string;

    /**
     * Retrieves the instance of the currently active administration area.
     * @return AdminAreaInterface
     */
    public function getActiveArea() : AdminAreaInterface;

    /**
     * Gets the active admin screen, if any.
     *
     * For example, this may return null when the
     * application is not running in UI mode.
     *
     * @return AdminScreenInterface|null
     */
    public function getActiveScreen() : ?AdminScreenInterface;

    /**
     * Gets the active admin screen, throwing an exception if none is active.
     * @return AdminScreenInterface
     */
    public function requireActiveScreen() : AdminScreenInterface;

    public function getPage() : ?UI_Page;

    public function getScreenByPath(string $path, bool $adminMode=true) : ?AdminScreenInterface;

    /**
     * @return string[]
     */
    public function getURLParamNames() : array;

    /**
     * @param UI_Page $page
     * @param array<string,string|number> $params
     * @return string
     */
    public function getPageURL(UI_Page $page, array $params = array()) : string;

    /**
     * Gets the full application version with tag, e.g. "3.2.6-beta".
     * @return string
     */
    public function getExtendedVersion() : string;

    /**
     * Gets the application version without a tag, e.g. "3.2.6" in "3.2.6-beta".
     * @return string
     */
    public function getVersion() : string;

    /**
     * Retrieves the application's minor version, e.g. "6" in "3.2.6".
     * @return string
     */
    public function getMinorVersion() : string;

    public function getUser() : Application_User;

    public function getApplication() : Application;

    public function getUI() : UI;

    /**
     * Must return an associative array with page name => administration class name
     * pairs to generate the main administration tree.
     *
     * @return array<string|class-string>
     */
    public function getAdminAreas() : array;
    public function areaExists(string $name) : bool;

    public function getAllowedAreas() : array;

    public function getAreas() : array;

    public function allowedToLogin() : bool;

    public function isUIFrameworkConfigured() : bool;

    public function configureAdminUIFramework() : void;

    public function describeAdminAreas() : Application_Driver_AdminInfo;

    public function createOAuth() : Application_OAuth;

    public function createArea(string $id) : Application_Admin_Area;

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    public function getAdminURLChangelog(array $params=array()) : string;

    /**
     * @param string|array<string,string|number>|AdminURLInterface|NULL $paramsOrURL
     * @return never
     */
    public function redirectTo(string|array|AdminURLInterface|NULL $paramsOrURL = null) : never;

    /**
     * Adds an informational message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @return never
     */
    public function redirectWithInfoMessage($message, $paramsOrURL = null);

    /**
     * Adds an error message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @return never
     */
    public function redirectWithErrorMessage($message, $paramsOrURL = null);

    /**
     * Adds a success message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @return never
     */
    public function redirectWithSuccessMessage($message, $paramsOrURL = null);

    public function resolveURLParam(AdminScreenInterface $screen) : string;
}
