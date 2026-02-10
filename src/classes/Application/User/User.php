<?php
/**
 * File containing the {@link Application_User} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User
 */

use Application\Application;
use Application\Countries\Rights\CountryRightsInterface;
use Application\Countries\Rights\CountryRightsTrait;
use Application\Driver\DriverException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Media\MediaRightsInterface;
use Application\Media\MediaRightsTrait;
use Application\NewsCentral\User\NewsRightsInterface;
use Application\NewsCentral\User\NewsRightsTrait;
use Application\Tags\TagsRightsInterface;
use Application\Tags\TagsRightsTrait;
use Application\User\LayoutWidth;
use Application\User\LayoutWidths;
use Application\User\Roles\RoleCollection;
use Application\User\UserException;
use Application\Users\Admin\Screens\UserSettingsArea;
use Application\Users\Rights\UserAdminRightsInterface;
use Application\Users\Rights\UserAdminRightsTrait;
use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;

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
abstract class Application_User
    implements
    Application_User_Interface,
    Application_Interfaces_Loggable,
    MediaRightsInterface,
    NewsRightsInterface,
    TagsRightsInterface,
    CountryRightsInterface,
    UserAdminRightsInterface
{
    use Application_Traits_Loggable;
    use MediaRightsTrait;
    use NewsRightsTrait;
    use TagsRightsTrait;
    use CountryRightsTrait;
    use UserAdminRightsTrait;

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

    public const RIGHTS_CORE = 'system_core';

    public const RIGHT_LOGIN = 'Login';
    public const RIGHT_TRANSLATE_UI = 'TranslateUI';
    public const RIGHT_DEVELOPER = 'Developer';
    public const RIGHT_QA_TESTER = 'QATester';

    /**
     * Stores user right definitions.
     * @var string[] List of right names.
     */
    protected array $rights = array();

   /**
    * Stores the user setting values
    * @var array<string,mixed>
    */
    protected array $settings = array();

    protected bool $settingsLoaded = false;

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
    private static ?Application_User_Rights $rightsManager = null;
    private Application_User_Rights $manager;

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
        $this->manager = $this->initRightsManager();

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
     * @param array<int|string,mixed> $default
     * @return array<int|string,mixed>
     */
    public function getArraySetting(string $name, array $default=array()) : array
    {
        $json = $this->getSetting($name);

        if(empty($json))
        {
            return array();
        }

        try
        {
            return JSONConverter::json2array($json);
        }
        catch (JSONConverterException $e)
        {
            $ex = new Application_Exception(
                'Could not decode stored array setting',
                sprintf(
                    'The setting [%s] for user [%s #%s] dit not decode into an array. Raw value: %s',
                    $name,
                    $this->getName(),
                    $this->getID(),
                    $json
                ),
                self::ERROR_CANNOT_DECODE_ARRAY_VALUE,
                $e
            );

            $ex->log();
        }

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

    public function resetSettings(?string $prefix=null) : void
    {
        $this->settingsLoaded = false;
        $this->settings = array();

        $this->storage->reset($prefix);
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

    /**
     * @return Application_User_Rights_Right[]
     * @throws Application_Exception
     */
    public function getRightObjects() : array
    {
        $result = array();
        foreach($this->rights as $rightName) {
            $result[] = $this->manager->getRightByID($rightName);
        }

        return $result;
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

        foreach ($this->storage->load() as $name => $value)
        {
            $name = (string)$name;
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
        return $this->manager->toArray();
    }

    /**
     * @param string $name
     * @return bool
     * @deprecated Use {@see self::rightExists()} instead.
     */
    public function roleExists(string $name) : bool
    {
        return $this->manager->roleIDExists($name);
    }

    public function rightExists(string $rightName) : bool
    {
        return $this->manager->rightIDExists($rightName);
    }

    /**
     * Retrieves an indexed array with translated role group names
     * @return array
     * @see getGrantableRoles()
     */
    public function getRoleGroups() : array
    {
        return $this->manager->getGroupNames();
    }

    /**
     * Checks if the user can use the specified role name. Uses the
     * roles as defined via the {@link getGrantableRoles()} method.
     * For ease of use, there are alias methods for all roles that
     * you can use as well.
     *
     * @param string $rightName
     */
    public function can(string $rightName) : bool
    {
        if(!in_array($rightName, $this->requestedRoles, true)) {
            $this->requestedRoles[] = $rightName;
        }

        if (!Application::isAuthenticationEnabled()) {
            return true;
        }

        if(!$this->rightExists($rightName)) {
            return false;
        }

        // first off, check if there is a role by this
        // name that the user is authorized for
        if ($this->hasRight($rightName)) {
            return true;
        }

        return $this->hasRightGrant($rightName);
    }

    public function hasRightGrant(string $rightName) : bool
    {
        foreach($this->getRightObjects() as $activeRight) {
            if($activeRight->hasGrant($rightName)) {
                return true;
            }
        }

        return false;
    }

   /**
    * Retrieves a list of all roles requested up to this point.
    * @return array
    */
    public function getRequestedRights() : array
    {
        return $this->requestedRoles;
    }

   /**
    * Creates a user instance for the specified user ID.
    *
    * @param int $user_id
    * @return Application_User
    * @throws Application_Exception
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
     * Creates the stub user used in the simulated session mode,
     * where the authentication layer is not used. It is not
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
     * It manages the statistics that are kept on a per-user basis,
     * like the number of times they logged in.
     *
     * @param DateTime|null $loginTime
     * @return $this
     * @throws Exception
     */
    final public function handleLoggedIn(?DateTime $loginTime=null) : self
    {
        if($loginTime === null)
        {
            $loginTime = new DateTime();
        }

        $this->getStatistics()->handleLoggedIn($loginTime);

        return $this;
    }

    public function handleScreenAccessed(AdminScreenInterface $screen)
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
     * @param string|string[]|mixed $rights Comma-separated rights list, or indexed array with right names. Any other value types are ignored.
     */
    public function setRights($rights) : void
    {
        $result = array();

        if(is_string($rights))
        {
            $result = ConvertHelper::explodeTrim(',', $rights);
        }
        else if(is_array($rights))
        {
            $result = $rights;
        }

        $this->rights = $result;
    }

    public function hasRight(string $rightName) : bool
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

    public function canLoginInMaintenanceMode() : bool
    {
        return $this->isDeveloper() || $this->isQATester();
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

    public function getUILocale() : LocaleInterface
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

    public function setUILocale(LocaleInterface $locale) : self
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
    public function isQATester() : bool { return $this->can(self::RIGHT_QA_TESTER); }

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
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = UserSettingsArea::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }

    public function getRightsManager() : Application_User_Rights
    {
        return $this->manager;
    }

    private function initRightsManager() : Application_User_Rights
    {
        if(isset(self::$rightsManager))
        {
            return self::$rightsManager;
        }

        self::$rightsManager = new Application_User_Rights();

        $this->registerRightGroups(self::$rightsManager);

        self::$rightsManager->registerGroup(
            self::RIGHTS_CORE,
            t('System core'),
            $this->registerRights_system_core(...)
        );

        // Give the developer all rights.
        self::$rightsManager
            ->getRightByID(self::RIGHT_DEVELOPER)
            ->grantRights(...self::$rightsManager->getRights()->getIDs());

        $collection = new RoleCollection(self::$rightsManager);
        $collection->register();

        $this->registerRoles(self::$rightsManager);

        return self::$rightsManager;
    }

    /**
     * Register available right groups using the rights manager instance.
     * Use the method {@see Application_User_Rights::registerGroup()} to
     * add groups.
     *
     * Right groups are used to bundle related rights together.
     * Typically, all rights for a specific area of the application will
     * be registered in a single group.
     *
     * @param Application_User_Rights $manager
     * @return void
     */
    abstract protected function registerRightGroups(Application_User_Rights $manager) : void;

    /**
     * Register developer-testable user roles using the rights manager instance.
     * These are used in local development only, to simulate user roles and
     * check the according right setup.
     *
     * @param Application_User_Rights $manager
     * @return void
     */
    abstract protected function registerRoles(Application_User_Rights $manager) : void;

    private function registerRights_system_core(Application_User_Rights_Group $group) : void
    {
        $group->setDescription(t('Application framework core rights.'));

        $group->registerRight(self::RIGHT_LOGIN, t('Log in'))
            ->actionAuthenticate()
            ->setDescription(t('Logging into the application.'));

        $group->registerRight(self::RIGHT_TRANSLATE_UI, t('Translate UI'))
            ->actionAdministrate()
            ->setDescription(t('Handle translations of the user interface.'));

        $group->registerRight(self::RIGHT_QA_TESTER, t('QA tester'))
            ->actionAdministrate()
            ->setDescription(sb()
                ->t('Marks the user as a QA tester, giving access to QA testing functionality.')
                ->t('This includes being able to access the interface when it is running in maintenance mode.')
            );

        $this->registerNewsRights($group);
        $this->registerTagRights($group);
        $this->registerMediaRights($group);
        $this->registerCountryRights($group);
        $this->registerUserAdminRights($group);

        $group->registerRight(self::RIGHT_DEVELOPER, t('Developer'))
            ->actionAdministrate()
            ->setDescription(sb()
                ->t('Marks the user as a developer, giving access to developer-only functionality.')
                ->t('It also allows enabling the application\'s developer mode for debugging purposes.')
                ->noteBold()
                ->t('This right automatically grants all other rights available in the application.')
            );
    }
}
