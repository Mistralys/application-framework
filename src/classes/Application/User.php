<?php
/**
 * File containing the {@link Application_User} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User
 */

use Application\Driver\DriverException;
use Application\User\LayoutWidth;
use Application\User\LayoutWidths;
use Application\User\UserException;
use AppLocalize\Localization;
use AppLocalize\Localization_Locale;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;

/**
 * Base user class that handles the user that is currently
 * logged in via the active session.
 *
 * NOTE: This cannot be used to manage other users, only
 * the authenticated user. To manager other users, use the
 * {@link Application_Users} collection.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_User implements Application_User_Interface, Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_CREATE_METHOD_NOT_IMPLEMENTED = 20001;
    public const ERROR_CREATE_SYSTEMUSER_METHOD_NOT_IMPLEMENTED = 20002;
    public const ERROR_NO_ROLES_DEFINED = 20003;
    public const ERROR_RIGHT_GROUP_METHOD_MISSING = 20005;
    public const ERROR_RECENT_ITEMS_CLASS_MISSING = 20006;
    public const ERROR_INVALID_RECENT_ITEMS_CLASS = 20007;
    public const ERROR_CANNOT_DECODE_ARRAY_VALUE = 20008;
    public const ERROR_UNKNOWN_STARTUP_AREA = 20009;

    public const STORAGE_TYPE_DB = 'DB';
    public const STORAGE_TYPE_FILE = 'File';

    /**
     * @see Application_User_Extended::registerRights_system_core()
     */
    public const RIGHTS_CORE = 'system_core';

    public const RIGHT_LOGIN = 'Login';
    public const RIGHT_TRANSLATE_UI = 'TranslateUI';
    public const RIGHT_DEVELOPER = 'Developer';

    /**
    * Stores user right definitions.
    * @var array
    * @see Application_User::initRoles()
    */
    protected $rights = array();

   /**
    * Stores the user setting values
    * @var array
    */
    protected $settings = array();

   /**
    * @var bool
    */
    protected $settingsLoaded = false;

   /**
    * @var Application_User_Storage
    */
    protected $storage;

   /**
    * @var string[]
    */
    protected array $requestedRoles = array();

    protected ?Application_User_Recent $recent = null;
    protected ?Application_User_Statistics $statistics = null;
    private ?Application_User_ScreenTracker $screenTracker = null;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array<string,string>
     */
    protected $data;

    private ?Application_User_Notepad $notepad = null;

    /**
     * Application_User constructor.
     * @param int $userID
     * @param array<string,string> $data
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function __construct(int $userID, array $data)
    {
        $this->id = $userID;
        $this->data = $data;

        $typeClass = ClassHelper::requireResolvedClass(sprintf(
            '%s_%s',
            Application_User_Storage::class,
            $this->getStorageType()
        ));

        $this->storage = ClassHelper::requireObjectInstanceOf(
            Application_User_Storage::class,
            new $typeClass($this)
        );
    }

   /**
    * Retrieves a user setting from the user's storage.
    *
    * @param string $name
    * @param string $default
    * @return string
    */
    public function getSetting(string $name, string $default = '') : string
    {
        if (!$this->settingsLoaded)
        {
            $this->loadSettings();
        }

        if (isset($this->settings[$name]))
        {
            return $this->validateSetting($name, $this->settings[$name]);
        }

        return $default;
    }

    /**
     * Sets an array setting: serializes the data to store it as a string.
     *
     * @param string $name
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function getArraySetting(string $name, array $default=array()) : array
    {
        $json = $this->getSetting($name);

        if(empty($json))
        {
            return array();
        }

        $decoded = json_decode($json, true);

        if(is_array($decoded))
        {
            return $decoded;
        }

        $ex = new Application_Exception(
            'Could not decode stored array setting',
            sprintf(
                'The setting [%s] for user [%s #%s] dit not decode into an array. Raw value: %s',
                $name,
                $this->getName(),
                $this->getID(),
                $json
            ),
            self::ERROR_CANNOT_DECODE_ARRAY_VALUE
        );

        $ex->log();

        return array();
    }

    public function setArraySetting(string $name, array $value) : void
    {
        $json = ConvertHelper::var2json($value);

        $this->setSetting($name, $json);
    }

    public function getBoolSetting(string $name, bool $default=false) : bool
    {
        $setting = $this->getSetting($name, ConvertHelper::bool2string($default));

        if(!empty($setting))
        {
            return ConvertHelper::string2bool($setting);
        }

        return $default;
    }

    public function setBoolSetting(string $name, bool $value) : void
    {
        $this->setSetting($name, ConvertHelper::bool2string($value));
    }

    public function setIntSetting(string $name, int $value) : void
    {
        $this->setSetting($name, strval($value));
    }

    public function setDateSetting(string $name, DateTime $value) : void
    {
        $this->setSetting($name, $value->format(DateTime::RFC3339_EXTENDED));
    }

    /**
     * Retrieves a date from a previously stored date value.
     *
     * NOTE: Must have been stored with setDateSetting().
     * @param string $name
     * @return DateTime|null
     * @throws Exception
     */
    public function getDateSetting(string $name) : ?DateTime
    {
        $date = $this->getSetting($name);

        if(!empty($date))
        {
            return new DateTime($date);
        }

        return null;
    }

    public function getIntSetting(string $name, int $default=0) : int
    {
        return intval($this->getSetting($name, strval($default)));
    }

    /**
     * @var array<string,bool>
     */
    protected $changedSettings = array();

   /**
    * Sets a user setting's value.
    *
    * NOTE: Must be saved using `saveSettings()` for the changes to be applied.
    *
    * @param string $name
    * @param string $value
    * @return bool Whether the setting value has changed.
    */
    public function setSetting(string $name, string $value) : bool
    {
        if (!$this->settingsLoaded)
        {
            $this->loadSettings();
        }

        $newValue = $this->validateSetting($name, $value);
        if(isset($this->settings[$name]) && $this->settings[$name] === $newValue) {
            return false;
        }

        $this->changedSettings[$name] = true;
        $this->settings[$name] = $newValue;

        return true;
    }

    public function resetSettings() : void
    {
        $this->settingsLoaded = false;
        $this->settings = array();

        $this->storage->reset();
    }

    protected function validateSetting(string $name, string $value) : string
    {
        $method = 'validateSetting_' . $name;

        if (method_exists($this, $method))
        {
            return (string)$this->$method($value);
        }

        return $value;
    }

    public function getID() : int
    {
        return $this->id;
    }

    public function getEmail() : string
    {
        return $this->data['email'];
    }

    public function getFirstname() : string
    {
        return $this->data['firstname'];
    }

    public function getLastname() : string
    {
        return $this->data['lastname'];
    }

    public function getForeignID() : string
    {
        return $this->data['foreign_id'];
    }

    public function getName() : string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * Retrieves an indexed array containing a list of the user's rights
     *
     * @return string[]
     */
    public function getRights() : array
    {
        return $this->rights;
    }

    public function saveSettings() : void
    {
        if(empty($this->changedSettings)) {
            $this->log('Save | Ignoring, no settings have been modified.');
            return;
        }

        $this->log('Save | Saving settings.');

        if (!$this->settingsLoaded) {
            $this->loadSettings();
        }

        $data = array();
        $names = array_keys($this->changedSettings);
        foreach($names as $name) {
            $data[$name] = $this->settings[$name];
        }

        $this->storage->save($data);

        $this->changedSettings = array();

        $this->log('Save | Complete.');
    }

    public function loadSettings() : void
    {
        if($this->settingsLoaded) {
            return;
        }

        $this->log('Loading settings.');

        $data = $this->storage->load();

        if (!is_array($data))
        {
            $this->logError('The stored settings data is not an array.');
            return;
        }

        foreach ($data as $name => $value)
        {
            $this->settings[$name] = $this->validateSetting($name, $value);
        }

        $this->log(sprintf('Loaded [%s] settings.', count($this->settings)));

        $this->settingsLoaded = true;
    }

    public function removeSetting(string $name) : void
    {
        if($this->settingsLoaded && isset($this->settings[$name])) {
            unset($this->settings[$name]);
        }

        $this->storage->removeKey($name);
    }

    /**
     * Retrieves an associative array containing information
     * about the available grantable roles for all users as
     * well as the roles that are granted automatically with
     * a role. The group key is used to group several roles
     * together, use the {@link getRoleGroups()} method to
     * retrieve the available groups.
     *
     * array(
     *         'RoleName' => array(
     *                 'label' => 'Human readable label',
     *                 'descr' => 'Detailed description if any',
     *                 'group' => 'Group name',
     *                 'grants' => array(
     *                         'RoleName2',
     *                         'RoleName3'
     *                 )
     *         ),
     *         [...]
     * )
     *
     * @return array
     * @see getRoleGroups()
     */
    public function getGrantableRoles() : array
    {
        self::initRoles();

        return self::$roles;
    }

    public function roleExists(string $name) : bool
    {
        self::initRoles();

        return isset(self::$roles[$name]);
    }

    /**
     * Retrieves an indexed array with translated role group names
     * @return array
     * @see getGrantableRoles()
     */
    public function getRoleGroups() : array
    {
        self::initRoles();
        $groups = array();
        foreach (self::$roles as $def) {
            if (!in_array($def['group'], $groups)) {
                $groups[] = $def['group'];
            }
        }

        sort($groups);

        return $groups;
    }

    /**
     * Used to cache the role definitions after initializing them
     * @var array|NULL
     * @see initRoles()
     */
    protected static ?array $roles = null;

    protected function initRoles() : void
    {
        if (isset(self::$roles)) {
            return;
        }

        self::$roles = $this->getRoleDefs();

        if(empty(self::$roles))
        {
            throw new Application_Exception(
                'No user roles defined',
                'The user class\' [getRoleDefs] method did not return any roles.',
                self::ERROR_NO_ROLES_DEFINED
            );
        }
    }

    abstract protected function getRoleDefs();

    /**
     * Checks if the user can use the specified role name. Uses the
     * roles as defined via the {@link getGrantableRoles()} method.
     * For ease of use, there are alias methods for all roles that
     * you can use as well.
     *
     * @param string $role
     */
    public function can(string $role) : bool
    {
        if(!in_array($role, $this->requestedRoles)) {
            $this->requestedRoles[] = $role;
        }

        if (!Application::isAuthenticationEnabled()) {
            return true;
        }

        if(!$this->roleExists($role)) {
            return false;
        }

        // first off, check if there is a role by this
        // name that the user is authorized for
        if ($this->hasRight($role)) {
            return true;
        }

        // next, we go through all roles and see if this
        // role is granted along with another role that
        // the user may be allowed for.
        foreach (self::$roles as $roleName => $roleDef)
        {
            if($roleName == $role) {
                continue;
            }

            if (in_array($role, $roleDef['grants']) && $this->hasRight($roleName)) {
                return true;
            }
        }

        return false;
    }

   /**
    * Retrieves a list of all roles requested up to this point.
    * @return array
    */
    public function getRequestedRoles() : array
    {
        return $this->requestedRoles;
    }

   /**
    * Creates a user instance for the specified user ID.
    *
    * @param int $user_id
    * @return Application_User
    * @throws Application_Exception|DBHelper_Exception
    * @deprecated Use {@see Application::createUser()} instead.
    */
    public static function createByID(int $user_id) : Application_User
    {
        return Application::createUser($user_id);
    }

    /**
     * @return Application_User
     * @deprecated Use {@see Application::createSystemUser()} instead.
     */
    public static function createSystemUser() : Application_User
    {
        return Application::createSystemUser();
    }

    /**
     * Creates the dummy user that is used in the simulated session
     * mode, where the authentication layer is not used. It is not
     * used in any other cases.
     *
     * @return Application_User
     * @deprecated Use {@see Application::createDummyUser()} instead.
     */
    public static function createDummyUser() : Application_User
    {
        return Application::createDummyUser();
    }

    /**
     * Checks whether the specified user ID exists
     * in the database.
     *
     * @param int $userID
     * @return bool
     * @deprecated
     */
    public static function userIDExists(int $userID) : bool
    {
        return Application::userIDExists($userID);
    }

    protected function getStorageType() : string
    {
        if(Application::isDatabaseEnabled()) {
            return self::STORAGE_TYPE_DB;
        }

        return self::STORAGE_TYPE_FILE;
    }

    /**
     * @return Application_User_Recent
     * @throws Application_Exception
     */
    public function getRecent() : Application_User_Recent
    {
        if(!isset($this->recent))
        {
            $this->recent = $this->createRecent();
        }

        return $this->recent;
    }

    /**
     * Creates the application-specific instance of the user
     * recent actions class.
     *
     * @return Application_User_Recent
     * @throws Application_Exception
     */
    private function createRecent() : Application_User_Recent
    {
        if(isset($this->recent))
        {
            return $this->recent;
        }

        $class = APP_CLASS_NAME.'_User_Recent';

        if(!class_exists($class))
        {
            throw new Application_Exception(
                'Missing user recent items manager class',
                sprintf(
                    'The class [%s] needs to be present to use the recent items manager.',
                    $class
                ),
                self::ERROR_RECENT_ITEMS_CLASS_MISSING
            );
        }

        $recent = new $class($this);

        if($recent instanceof Application_User_Recent)
        {
            $this->recent = $recent;
            return $this->recent;
        }

        throw new Application_Exception(
            'Invalid items manager class',
            sprintf(
                'The class [%s] must extend the [%s] base class.',
                $class,
                Application_User_Recent::class
            ),
            self::ERROR_INVALID_RECENT_ITEMS_CLASS
        );
    }

    public function getStatistics() : Application_User_Statistics
    {
        if(!isset($this->statistics))
        {
            $this->statistics = new Application_User_Statistics($this);
        }

        return $this->statistics;
    }

    /**
     * This should be called when the user has been successfully logged in.
     * It manages the statistics that are kept by user, like the amount of
     * times they logged in.
     *
     * @param DateTime|null $loginTime
     * @return $this
     * @throws Exception
     */
    public final function handleLoggedIn(?DateTime $loginTime=null)
    {
        if($loginTime === null)
        {
            $loginTime = new DateTime();
        }

        $this->getStatistics()->handleLoggedIn($loginTime);

        return $this;
    }

    public function handleScreenAccessed(Application_Admin_ScreenInterface $screen)
    {
        $this->getScreenTracker()->handleScreenAccessed($screen);
        return $this;
    }

    public function getScreenTracker() : Application_User_ScreenTracker
    {
        if(!isset($this->screenTracker))
        {
            $this->screenTracker = new Application_User_ScreenTracker($this);
        }

        return $this->screenTracker;
    }

    /**
     * Clears the internal cache of items that are cached
     * during every request, like the settings or recent items.
     *
     * This is mostly used in the unit tests to reset the
     * user and force reloading items.
     */
    public function clearCache() : void
    {
        $this->log('Clearing the internal cache.');

        $this->recent = null;
        $this->statistics = null;
        $this->settingsLoaded = false;
        $this->changedSettings = array();
        $this->settings = array();
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'User [%s]',
            $this->getID()
        );
    }

    /**
     * Sets the user's rights.
     * @param string|array $rights Comma-separated rights list, or indexed array with right names.
     */
    public function setRights($rights) : void
    {
        $result = array();

        if(is_string($rights))
        {
            $result = explode(',', $rights);
            array_map('trim', $result);
        }
        else if(is_array($rights))
        {
            $result = $rights;
        }

        $this->rights = $result;
    }

    public function hasRight($rightName) : bool
    {
        return in_array($rightName, $this->getRights());
    }

    // region: Settings

    public const SETTING_DEVELOPER_MODE = 'developer_mode';
    public const SETTING_UI_LOCALE = 'locale';
    public const SETTING_UI_WIDTH = 'layout_width';
    public const SETTING_STARTUP_TAB = 'startup_tab';
    public const SETTING_DARK_MODE = 'dark_mode';

    public function isDarkModeEnabled() : bool
    {
        return $this->getSetting(self::SETTING_DARK_MODE) === 'true';
    }

    public function setDarkModeEnabled(bool $enabled) : self
    {
        $this->setSetting(self::SETTING_DARK_MODE, ConvertHelper::boolStrict2string($enabled));
        return $this;
    }

    public function isDeveloper() : bool
    {
        if(Application::isDemoMode()) {
            return false;
        }

        return $this->can(self::RIGHT_DEVELOPER);
    }

    public function isDeveloperModeEnabled() : bool
    {
        if(!$this->isDeveloper()) {
            return false;
        }

        return $this->getBoolSetting(self::SETTING_DEVELOPER_MODE);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setDeveloperModeEnabled(bool $enabled) : self
    {
        if($this->isDeveloper()) {
            $this->setBoolSetting(self::SETTING_DEVELOPER_MODE, $enabled);
        }

        return $this;
    }

    public function getUILocale() : Localization_Locale
    {
        return Localization::getAppLocaleByName($this->getUILocaleName());
    }

    public function getUILocaleName() : string
    {
        $locale = $this->getSetting(self::SETTING_UI_LOCALE);

        if(Localization::appLocaleExists($locale)) {
            return $locale;
        }

        return Localization::getAppLocaleName();
    }

    public function setUILocale(Localization_Locale $locale) : self
    {
        $this->setSetting(self::SETTING_UI_LOCALE, $locale->getName());
        return $this;
    }

    public function setLayoutWidth(LayoutWidth $width) : self
    {
        $this->setSetting(self::SETTING_UI_WIDTH, $width->getID());
        return $this;
    }

    public function getLayoutWidthID() : string
    {
        return LayoutWidths::getInstance()->getIDOrDefault($this->getSetting(self::SETTING_UI_WIDTH));
    }

    public function getLayoutWidth() : LayoutWidth
    {
        return LayoutWidths::getInstance()->getByID($this->getLayoutWidthID());
    }

    /**
     * Sets the URL name of the admin area to open by default.
     *
     * @param string $name
     * @return $this
     * @throws DriverException
     * @throws UserException {@see self::ERROR_UNKNOWN_STARTUP_AREA}
     */
    public function setStartupScreenName(string $name) : self
    {
        if(Application_Driver::getInstance()->areaExists($name)) {
            $this->setSetting(self::SETTING_STARTUP_TAB, $name);
            return $this;
        }

        throw new UserException(
            'Unknown startup admin area.',
            sprintf(
                'The admin area [%s] does not exist.',
                $name
            ),
            self::ERROR_UNKNOWN_STARTUP_AREA
        );
    }

    /**
     * Retrieves the URL name of the admin area the user
     * wishes to open by default.
     *
     * @return string
     * @throws DriverException
     */
    public function getStartupScreenName() : string
    {
        $name = $this->getSetting(self::SETTING_STARTUP_TAB);
        $driver = Application_Driver::getInstance();

        if($driver->areaExists($name)) {
            return $name;
        }

        return $driver
            ->getAppSet()
            ->getDefaultArea()
            ->getURLName();
    }

    // endregion

    public function canTranslateUI() : bool { return $this->can(self::RIGHT_TRANSLATE_UI); }
    public function canLogin() : bool { return $this->can(self::RIGHT_LOGIN); }


    // region: News management

    public const RIGHT_CREATE_NEWS = 'CreateNews';
    public const RIGHT_CREATE_NEWS_ALERTS = 'CreateAlerts';
    public const RIGHT_EDIT_NEWS = 'EditNews';
    public const RIGHT_DELETE_NEWS = 'DeleteNews';
    public const RIGHT_VIEW_NEWS = 'ViewNews';

    public function canViewNews() : bool { return $this->can(self::RIGHT_VIEW_NEWS); }
    public function canCreateNews() : bool { return $this->can(self::RIGHT_CREATE_NEWS); }
    public function canCreateNewsAlerts() : bool { return $this->can(self::RIGHT_CREATE_NEWS_ALERTS); }
    public function canEditNews() : bool { return $this->can(self::RIGHT_EDIT_NEWS); }
    public function canDeleteNews() : bool { return $this->can(self::RIGHT_DELETE_NEWS); }

    // endregion

    // region: Tags management

    public const RIGHT_CREATE_TAGS = 'CreateTags';
    public const RIGHT_EDIT_TAGS = 'EditTags';
    public const RIGHT_DELETE_TAGS = 'DeleteTags';
    public const RIGHT_VIEW_TAGS = 'ViewTags';

    public function canViewTags() : bool { return $this->can(self::RIGHT_VIEW_TAGS); }
    public function canCreateTags() : bool { return $this->can(self::RIGHT_CREATE_TAGS); }
    public function canEditTags() : bool { return $this->can(self::RIGHT_EDIT_TAGS); }
    public function canDeleteTags() : bool { return $this->can(self::RIGHT_DELETE_TAGS); }

    // endregion

    // region: Media management

    public const RIGHT_CREATE_MEDIA = 'CreateMedia';
    public const RIGHT_EDIT_MEDIA = 'EditMedia';
    public const RIGHT_DELETE_MEDIA = 'DeleteMedia';
    public const RIGHT_VIEW_MEDIA = 'ViewMedia';

    public function canViewMedia() : bool { return $this->can(self::RIGHT_VIEW_MEDIA); }
    public function canCreateMedia() : bool { return $this->can(self::RIGHT_CREATE_MEDIA); }
    public function canEditMedia() : bool { return $this->can(self::RIGHT_EDIT_MEDIA); }
    public function canDeleteMedia() : bool { return $this->can(self::RIGHT_DELETE_MEDIA); }

    // endregion

    public function isSystemUser() : bool
    {
        return Application::isSystemUserID($this->id);
    }

    public function getNotepad() : Application_User_Notepad
    {
        if(!isset($this->notepad))
        {
            $this->notepad = new Application_User_Notepad($this);
        }

        return $this->notepad;
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Settings::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }
}
