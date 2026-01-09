<?php
/**
 * @package Application
 * @subpackage Sessions
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\AppFactory\AppFactoryException;
use Application\Application;
use Application\Session\Events\BeforeLogOutEvent;
use Application\Session\Events\SessionStartedEvent;
use Application\Session\Events\UserAuthenticatedEvent;
use Application\User\Role\DeveloperRole;
use AppUtils\Request;
use function AppUtils\parseURL;

/**
 * Base session class: defines the core mechanisms of the
 * available session systems. Also included in the mechanism
 * is triggering the authentication and storing the user
 * information in the session.
 *
 * NOTE: To add session event handlers, see the offline
 * event class: {@see \Application\Session\Events\SessionInstantiatedEvent}.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen::initSession()
 */
abstract class Application_Session_Base implements Application_Session
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;

    public const ERROR_ADMIN_RIGHTS_PRESET_MISSING = 22201;
    public const ERROR_AUTH_DID_NOT_RETURN_USER = 22205;
    public const ERROR_NO_USER_AVAILABLE = 22208;
    public const ERROR_PRESET_NOT_DEFINED = 22209;
    public const ERROR_CANNOT_AUTHENTICATE_TWICE = 22210;

    public const REQUEST_PARAM_RIGHTS_PRESET = 'roleID';

    public const KEY_NAME_USER_ID = 'user_id';
    public const KEY_NAME_USER_RIGHTS = 'user_rights';
    public const KEY_NAME_SIMULATED_ID = 'simulate_user_id';
    public const KEY_NAME_RIGHTS_PRESET = 'simulate_rights_preset';
    public const KEY_NAME_AUTH_RETURN_URI = 'auth_return_url';

    public const LOGOUT_REASON_USER_REQUEST = 75901;
    public const LOGOUT_REASON_LOGIN_NOT_ALLOWED = 75902;


    /**
     * @var array<int,string>
     */
    protected array $simulateableUsers = array(
        Application::USER_ID_SYSTEM => 'System',
        Application::USER_ID_DUMMY => 'Dummy'
    );

    protected int $defaultSimulatedUser = Application::USER_ID_SYSTEM;
    protected ?Application_User $user = null;
    protected bool $started = false;

    /**
     * @see Application_Bootstrap_Screen::initSession()
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init() : void
    {

    }

    final public function start() : self
    {
        $this->log('Starting the session.');
        $this->_start();
        $this->log('Session started with ID [%s].', $this->getID());

        $this->started = true;
        $this->triggerStarted();

        return $this;
    }

    abstract protected function _start() : void;

    final public function destroy() : self
    {
        $this->log('Destroying the session.');

        $this->_destroy();

        return $this;
    }

    abstract protected function _destroy() : void;

    public function isStarted() : bool
    {
        return $this->started;
    }

    /**
     * The name is a combination of the session name and the type ID.
     * This way, a CAS session will have storage separate from a no-auth
     * session. Additionally, turning off authentication or enabling
     * session simulation mode will also use separate names and thus storage.
     *
     * @return string
     */
    final public function getName() : string
    {
        // Use a separate session prefix when using the request log,
        // to ensure that it has a separate session storage.
        if(defined(Application_Bootstrap_Screen_RequestLog::CONST_REQUEST_LOG_RUNNING)) {
            $name = $this->_getName().'_reqlog';
        } else {
            $name = $this->_getName();
        }

        $name .= '_'.$this->getAuthTypeID();

        if(!Application::isAuthenticationEnabled()) {
            $name .= '_authOff';
        }

        if(Application::isSessionSimulated()) {
            $name .= '_sim';
        }

        return $name;
    }

    abstract protected function _getName() : string;

    final public function logOut(int $reasonID=self::LOGOUT_REASON_USER_REQUEST): void
    {
        if(!isset($this->user)) {
            return;
        }

        $this->log('Logout requested, logging the user out.');

        $this->triggerBeforeLogOut($this->user, $reasonID);

        // Reset the developer mode
        $this->user->setDeveloperModeEnabled(false);
        $this->user->saveSettings();

        $this->log('Destroying the session.');
        $this->destroy();

        self::redirectToLogout($reasonID);
    }

    private static bool $redirectsEnabled = true;

    public static function setRedirectsEnabled(bool $enabled) : void
    {
        self::$redirectsEnabled = $enabled;
    }

    public static function redirectToLogout(int $reasonCode) : void
    {
        if(self::$redirectsEnabled) {
            Application::redirect(APP_URL . '/logged-out.php?reason=' . $reasonCode);
        }
    }

    protected function redirectToRegistrationDisabled() : void
    {
        if(self::$redirectsEnabled) {
            Application::redirect(APP_URL . '/registration-disabled.php');
        }
    }

    // region: Authentication

    /**
     * @inheritDoc
     *
     * @throws Application_Exception
     * @throws Application_Session_Exception {@see self::ERROR_CANNOT_AUTHENTICATE_TWICE}
     */
    final public function authenticate() : Application_User
    {
        $this->log('Authenticate | Starting authentication [Auth enabled: %s].', bool2string($this->isAuthEnabled()));

        if(isset($this->user)) {
            throw new Application_Session_Exception(
                'Cannot authenticate user a second time.',
                '',
                self::ERROR_CANNOT_AUTHENTICATE_TWICE
            );
        }

        $userID = $this->getUserID();

        $this->log(sprintf('Authenticate | Stored user ID is [%s].', $userID));

        // Starts the authentication process. This ends either by
        // storing the user ID in the session via `storeUser()`,
        // or via a redirect to an error page.
        if ($userID === 0) {
            $this->runAuthentication();
            $userID = $this->getUserID();
        }

        $this->log(sprintf('Authenticate | Resolved user ID to be [%s].', $userID));

        $this->user = $this->loadUserByID($userID);

        $this->handleUserLoaded();

        return $this->user;
    }

    public function isAuthEnabled() : bool
    {
        return
            Application::isAuthenticationEnabled()
            &&
            !Application::isSessionSimulated();
    }

    /**
     * Runs the authentication process in the following steps:
     *
     * 1. {@see self::sendAuthenticationCallbacks()}
     * 2. {@see self::finalizeAuthentication()}
     *
     * @return void
     * @throws AppFactoryException
     * @throws Application_Session_Exception
     */
    private function runAuthentication() : void
    {
        if($this->isAuthEnabled())
        {
            $this->log('Authenticate | No user ID found in the session, running authentication.');

            $returnURI = $_SERVER['REQUEST_URI'] ?? '(none available)';
            $this->setValue(self::KEY_NAME_AUTH_RETURN_URI, $returnURI);
            $this->log('Authenticate | Return URI is [%s].', $returnURI);

            $user = $this->sendAuthenticationCallbacks();
        }
        else
        {
            $this->log('Authenticate | Auth is disabled - using the system user.');
            $user = AppFactory::createUsers()->getSystemUser();
        }

        if($user !== null)
        {
            $this->finalizeAuthentication($user);
            return;
        }

        throw new Application_Session_Exception(
            'Authentication layer failure',
            'The authentication did not return a user. The authenticator should handle displaying a message to the user if the authentication fails.',
            self::ERROR_AUTH_DID_NOT_RETURN_USER
        );
    }

    /**
     * Handles the logic and callback loops to authenticate the user.
     *
     * It also handles registering new users when the user being
     * authenticated is not in the known users list yet (even if they
     * are not allowed to log in, this is a later step).
     */
    abstract protected function sendAuthenticationCallbacks() : ?Application_Users_User;

    /**
     * Stores the specified user in the session as the authenticated user
     * after the authentication process has completed successfully.
     *
     * @param Application_Users_User $user
     * @throws Application_Exception
     */
    private function finalizeAuthentication(Application_Users_User $user) : void
    {
        $userID = $user->getID();

        $this->log(sprintf('User [%s] | Authentication successful.', $userID));

        // Unpack the user, as processes after this may need to access
        // the instance. The redirect, for example, needs to know whether
        // the user is a developer.
        $unpacked = $this->loadUserByID($userID);

        // Store the user ID in the session. In no-auth sessions, this
        // will be the system user. Because sessions are namespaced to
        // the authentication type, this will not conflict with other
        // sessions. A No-Auth session can coexist with a CAS session,
        // for example.
        $this->setValue(self::KEY_NAME_USER_ID, $userID);

        $this->triggerUserAuthenticated($unpacked);

        $this->redirectToReturnURI();
    }

    /**
     * @return void
     * @throws Application_Exception
     */
    protected function redirectToReturnURI() : void
    {
        // Only redirect if this was actually part of an
        // authentication callback.
        if(!$this->isAuthEnabled() || isCLI()) {
            $this->log('ReturnURI | Ignoring, auth is disabled.');
            return;
        }

        $this->log('ReturnURI | Redirecting to the initially requested URL.');
        Application::redirect($this->unpackTargetURL());
    }

    // endregion

    /**
     * Retrieves the user ID as stored in the session, if any.
     *
     * @return int The user ID, or 0 if none.
     */
    public function getUserID() : int
    {
        return (int)$this->getValue(self::KEY_NAME_USER_ID);
    }

    /**
     * Unpacks the user instance from the user ID stored in the session.
     * Returns an application-driver-specific object, e.g. `DriverName_User`.
     *
     * @param int $userID
     * @return Application_User
     * @throws Application_Exception
     */
    private function loadUserByID(int $userID) : Application_User
    {
        $this->log('User [%s] | Unpacking the user and their rights.', $userID);

        $user = Application::createUser($userID);
        $user->setRights($this->unpackRights($user));

        return $user;
    }

    /**
     * Checks whether a role preset change has been
     * requested. This can only be done in devel mode,
     * so only a developer can initially enable this.
     *
     * It is necessary to switch back to a developer
     * role to turn the developer mode back off (because
     * non-developer roles do not have the right to
     * do so).
     */
    private function checkRolePresetChange() : void
    {
        if(!isDevelMode()) {
            return;
        }

        $presetName = $this->getPresetByRequest();

        if (empty($presetName)) {
            return;
        }

        $this->log(sprintf('Authenticate | Selected rights preset [%s].', $presetName));

        $this->setValue(self::KEY_NAME_RIGHTS_PRESET, $presetName);

        UI::getInstance()->addInfoMessage(t('Switched the role preset to %1$s.', sb()->code($presetName)));

        // The request object is not fully configured yet, do this manually
        $refresh = str_replace('&amp;', '&', Request::getInstance()->setBaseURL(APP_URL)->buildRefreshURL(array(), array(self::REQUEST_PARAM_RIGHTS_PRESET)));

        Application::redirect($refresh);
    }

    private function unpackRights(Application_User $user) : array
    {
        if(!$this->isAuthEnabled() || $user->isDeveloperModeEnabled())
        {
            $rights = $this->fetchSimulatedRights();
        }
        else
        {
            $rights = $this->fetchRights($user);
        }

        $userID = $user->getID();

        $this->log(sprintf('User [%s] | UnpackRights | Found [%s] rights.', $userID, count($rights)));
        $this->logData($rights);

        $this->setValue(self::KEY_NAME_USER_RIGHTS, implode(',', $rights));

        return $rights;
    }

    /**
     * Determines which URL to redirect to after authentication.
     *
     * The session automatically stores the URL accessed originally
     * before the authentication was triggered, and redirects to
     * that URL after authentication.
     *
     * @return string
     */
    protected function unpackTargetURL() : string
    {
        // The return URI is the value of $_SERVER['REQUEST_URI'].
        // Example: "/?foo=bar"
        $returnURI = $this->getValue(self::KEY_NAME_AUTH_RETURN_URI);
        $targetURI = '';

        $appURL = parseURL(APP_URL);

        if(is_string($returnURI) && !empty($returnURI))
        {
            $this->unsetValue(self::KEY_NAME_AUTH_RETURN_URI);
            $targetURI = $returnURI;
        }

        // The URL is built dynamically using the application's
        // base URL, and the target request URI (if any).
        return sprintf(
            '%s://%s/%s',
            $appURL->getScheme(),
            $appURL->getHost(),
            ltrim($targetURI, '/')
        );
    }

    /**
     * @return Application_User_Rights_Role[]
     */
    public function getRightPresets() : array
    {
        return $this->getSystemRightsManager()->getRoles();
    }

    public function getSystemRightsManager() : Application_User_Rights
    {
        return Application::createSystemUser()->getRightsManager();
    }

    public function getRightPreset() : string
    {
        if (!$this->presetExists(DeveloperRole::ROLE_ID))
        {
            throw new Application_Exception(
                'Admin rights preset not defined',
                sprintf(
                    'You have to define the [%s] rights preset in the [%s] class for the simulated session mode to work.',
                    DeveloperRole::ROLE_ID,
                    get_class($this)
                ),
                self::ERROR_ADMIN_RIGHTS_PRESET_MISSING
            );
        }

        $select = DeveloperRole::ROLE_ID;

        $sessionPreset = $this->getPresetBySession();
        if(!empty($sessionPreset))
        {
            $select = $sessionPreset;
        }

        return (string)$this->getValue(self::KEY_NAME_RIGHTS_PRESET, $select);
    }

    public function getPresetBySession() : string
    {
        $preset = $this->getValue(self::KEY_NAME_RIGHTS_PRESET);

        if(!empty($preset) && $this->presetExists($preset))
        {
            return $preset;
        }

        return '';
    }

    public function getPresetByRequest() : string
    {
        if(isset($_REQUEST[self::REQUEST_PARAM_RIGHTS_PRESET]) && $this->presetExists($_REQUEST[self::REQUEST_PARAM_RIGHTS_PRESET]))
        {
            return $_REQUEST[self::REQUEST_PARAM_RIGHTS_PRESET];
        }

        return '';
    }

    public function presetExists(string $presetID) : bool
    {
        return $this->getSystemRightsManager()->roleIDExists($presetID);
    }

    /**
     * Gets the list of rights currently stored in the session.
     * @return string Comma-separated list of right names.
     */
    public function getRightsString() : string
    {
        return (string)$this->getValue(self::KEY_NAME_USER_RIGHTS);
    }

    /**
     * @return string[]
     * @throws Application_Exception
     * @throws Application_Session_Exception
     */
    public function fetchSimulatedRights() : array
    {
        $roleID = $this->getRightPreset();
        $manager = $this->getSystemRightsManager();

        if($manager->roleIDExists($roleID)) {
            return $manager->getRoleByID($roleID)->getRightIDs();
        }

        throw new Application_Session_Exception(
            'Right preset not defined',
            sprintf(
                'The right preset [%s] does not exist in class [%s].',
                $roleID,
                get_class($this)
            ),
            self::ERROR_PRESET_NOT_DEFINED
        );
    }

    /**
     * @return array<int,string>
     */
    public function getSimulateableUsers() : array
    {
        return $this->simulateableUsers;
    }

    public function getLogIdentifier(): string
    {
        return getClassTypeName($this);
    }

    public function getUser() : ?Application_User
    {
        return $this->user;
    }

    private function handleUserLoaded() : void
    {
        $this->checkRolePresetChange();
        $this->checkLogout();
    }

    private function checkLogout() : void
    {
        if (isset($_REQUEST['logout']) && string2bool($_REQUEST['logout']) === true)
        {
            $this->logOut();
        }
    }

    /**
     * @return Application_User
     * @throws Application_Session_Exception
     */
    public function requireUser() : Application_User
    {
        $user = $this->getUser();

        if($user !== null)
        {
            return $user;
        }

        throw new Application_Session_Exception(
            'No user has been authenticated yet',
            '',
            self::ERROR_NO_USER_AVAILABLE
        );
    }

    /**
     * Checks if the specified user already exists, or if it should
     * be added to the database. If registration is disabled, redirects
     * the user to the registration disabled screen.
     *
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $foreignID
     * @return Application_Users_User
     */
    protected function registerUser(string $email, string $firstname, string $lastname, string $foreignID='') : Application_Users_User
    {
        $this->log(sprintf(
            'User [%s] | Registering the user.',
            $email
        ));

        $users = AppFactory::createUsers();
        $user = $users->getByEmail($email);

        if($user !== null)
        {
            $this->log(sprintf(
                'User [%s] | User already exists in the database.',
                $email
            ));

            return $this->updateUser($user, $firstname, $lastname, $foreignID);
        }

        // User isn't found in DB, and registration is disabled: redirect
        // to the registration disabled screen.
        if(!$this->isRegistrationEnabled())
        {
            $this->log(sprintf(
                'User [%s] | Cannot register new user, registration is disabled.',
                $email
            ));

            $this->redirectToRegistrationDisabled();
        }

        return $this->addUser($email, $firstname, $lastname, $foreignID);
    }

    protected function addUser(string $email, string $firstname, string $lastname, string $foreignID) : Application_Users_User
    {
        $this->log(sprintf('User [%s] | Inserting new user in the database.', $email));

        $users = AppFactory::createUsers();

        DBHelper::startTransaction();
        $user = $users->createNewUser($email, $firstname, $lastname, $foreignID);
        DBHelper::commitTransaction();

        return $user;
    }

    private function updateUser(Application_Users_User $user, string $firstname, string $lastname, string $foreignID) : Application_Users_User
    {
        $user->setFirstName($firstname);
        $user->setLastName($lastname);
        $user->setForeignID($foreignID);

        if($user->isModified())
        {
            $this->log(sprintf(
                'User [%s] | The user\'s data has changed, saving new data to database.',
                $user->getEmail()
            ));

            DBHelper::startTransaction();
            $user->save();
            DBHelper::commitTransaction();
        }

        return $user;
    }

    // region: Event handling

    public const EVENT_USER_AUTHENTICATED = 'UserAuthenticated';
    public const EVENT_BEFORE_LOG_OUT = 'BeforeLogOut';
    public const EVENT_STARTED = 'SessionStarted';

    /**
     * Adds a listener for the {@see self::EVENT_USER_AUTHENTICATED} event,
     * which is called when a user is freshly authenticated in the session.
     *
     * The callback gets a single parameter:
     *
     * 1) The event instance, {@see UserAuthenticatedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onUserAuthenticated(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_USER_AUTHENTICATED, $callback);
    }

    /**
     * Adds a listener for the {@see self::EVENT_BEFORE_LOG_OUT} event,
     * which is called right before the user is logged out.
     *
     * The callback gets a single parameter:
     *
     * 1) The event instance, {@see BeforeLogOutEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onBeforeLogOut(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_BEFORE_LOG_OUT, $callback);
    }

    /**
     * Adds a listener for the {@see self::EVENT_STARTED} event,
     * which is called when the session has been started.
     *
     * The callback gets a single parameter:
     *
     * 1) The event instance, {@see SessionStartedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onSessionStarted(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_STARTED, $callback);
    }

    private function triggerUserAuthenticated(Application_User $user) : void
    {
        $this->triggerEvent(
            self::EVENT_USER_AUTHENTICATED,
            array($user),
            UserAuthenticatedEvent::class
        );
    }

    private function triggerBeforeLogOut(Application_User $user, int $reasonID) : void
    {
        $this->triggerEvent(
            self::EVENT_BEFORE_LOG_OUT,
            array($user, $reasonID),
            BeforeLogOutEvent::class
        );
    }

    private function triggerStarted() : void
    {
        $this->triggerEvent(
            self::EVENT_STARTED,
            array($this),
            SessionStartedEvent::class
        );
    }

    // endregion
}
