<?php
/**
 * File containing the {@link Application_Driver_Interface} interface.
 * 
 * @package Application
 * @subpackage Interfaces
 * @see Application_Driver_Interface
 */

use Application\Interfaces\Admin\AdminScreenInterface;

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

    public function getClassesFolder() : string;

    public function getConfigFolder() : string;

    public function getActiveArea() : Application_Admin_Area;

    public function getActiveScreen() : AdminScreenInterface;

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

    public function getExtendedVersion() : string;

    public function getVersion() : string;

    public function getMinorVersion() : string;

    public function getUser() : Application_User;

    public function getApplication() : Application;

    public function getUI() : UI;

    /**
     * Must return an associative array with page name => administration class name
     * pairs to generate the main administration tree.
     *
     * @return array<string,string>
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
     * @param string|array<string,string|number>|NULL $paramsOrURL
     * @return never
     */
    public function redirectTo($paramsOrURL = null);

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
