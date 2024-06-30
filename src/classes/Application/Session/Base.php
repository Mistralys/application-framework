<?php
/**
 * File containing the {@see Application_Session_Base} class.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session_Base
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Session\Events\BeforeLogOutEvent;
use Application\Session\Events\SessionStartedEvent;
use Application\Session\Events\UserAuthenticatedEvent;
use AppUtils\ConvertHelper;
use function AppUtils\parseURL;

/**
 * Base session class: defines the core mechanisms of the
 * available session systems. Also included in the mechanism
 * is triggering the authentication, and storing the user
 * information in the session.
 *
 * NOTE: To add session event handlers, see the offline
 * event class: {@see \Application\OfflineEvents\SessionInstantiatedEvent}.
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
    public const ERROR_ONLY_FOR_SIMULATED_SESSION = 22202;
    public const ERROR_NO_RIGHT_PRESETS_PRESENT = 22203;
    public const ERROR_AUTH_DID_NOT_RETURN_USER = 22205;
    public const ERROR_INVALID_USER_CLASS = 22206;
    public const ERROR_INVALID_USER_ID = 22207;
    public const ERROR_NO_USER_AVAILABLE = 22208;
    public const ERROR_PRESET_NOT_DEFINED = 22209;

    public const KEY_NAME_USER_ID = 'user_id';
    public const KEY_NAME_USER_RIGHTS = 'user_rights';
    public const KEY_NAME_SIMULATED_ID = 'simulate_user_id';
    public const KEY_NAME_RIGHTS_PRESET = 'simulate_rights_preset';
    public const KEY_NAME_AUTH_RETURN_URI = 'auth_return_url';

    public const ADMIN_PRESET_ID = 'Admin';
    public const QA_TESTING_PRESET_ID = 'QATesting';

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

    /**
     * @var array<string,array<int,string>>
     */
    protected array $rightPresets = array();

    protected ?Application_User $user = null;

    final public function start() : self
    {
        $this->log('Starting the session.');
        $this->_start();
        $this->log('Session started with ID [%s].', $this->getID());

        $this->triggerStarted();

        return $this;
    }

    abstract protected function _start() : void;

    abstract protected function handleLogout(array $clearKeys=array()) : void;

    /**
     * Handles the logic to log in the user. This must
     * call `storeUser()` when a user has been successfully
     * logged in.
     *
     * It should also handle registering new users, if
     * this is applicable.
     */
    abstract protected function handleLogin() : ?Application_Users_User;

    /**
     * @see Application_Bootstrap_Screen::initSession()
     */
    public function __construct()
    {
    }

    public function logOut(int $reasonID=self::LOGOUT_REASON_USER_REQUEST): void
    {
        if(!isset($this->user)) {
            return;
        }

        $this->log('Logout requested, logging the user out.');

        $this->triggerBeforeLogOut($this->user, $reasonID);

        $this->handleLogout(array(
            self::KEY_NAME_AUTH_RETURN_URI,
            self::KEY_NAME_RIGHTS_PRESET,
            self::KEY_NAME_USER_ID,
            self::KEY_NAME_SIMULATED_ID,
            self::KEY_NAME_USER_RIGHTS
        ));

        self::redirectToLogout($reasonID);
    }

    public static function redirectToLogout(int $reasonCode) : void
    {
        Application::redirect(APP_URL . '/logged-out.php?reason='.$reasonCode);
    }

    protected function redirectToRegistrationDisabled() : void
    {
        Application::redirect(APP_URL.'/registration-disabled.php');
    }

    private function initAuthentication() : void
    {
        $returnURI = $_SERVER['REQUEST_URI'] ?? '(none available)';

        $this->log('No user ID found in the session, initiating login sequence.');
        $this->log('Return URI is [%s].', $returnURI);

        $this->setValue(self::KEY_NAME_AUTH_RETURN_URI, $returnURI);

        if(Application::isAuthenticationEnabled()) {
            $user = $this->handleLogin();
        } else {
            $user = AppFactory::createUsers()->getByID(Application::USER_ID_SYSTEM);
        }

        if($user !== null)
        {
            $this->storeUser($user);
            return;
        }

        throw new Application_Exception(
            'Authentication layer failure',
            'The authentication did not return a user. The authenticator should handle displaying a message to the user if the authentication fails.',
            self::ERROR_AUTH_DID_NOT_RETURN_USER
        );
    }

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
     * @return Application_User
     * @throws Application_Exception
     */
    private function unpackUser() : Application_User
    {
        $userID = $this->getUserID();

        $this->log('User [%s] | Unpacking the user and their rights.', $userID);

        $user = Application::createUser($userID);
        $user->setRights($this->unpackRights());

        return $user;
    }

    /**
     * Stores the specified user in the session as the authenticated user
     * after the authentication process has completed successfully.
     *
     * @param Application_Users_User $user
     */
    private function storeUser(Application_Users_User $user) : void
    {
        $userID = $user->getID();

        $this->log(sprintf('User [%s] | Authentication successful.', $userID));

        if(!Application::isSessionSimulated())
        {
            $rights = $this->fetchRights($user);
        }
        else
        {
            $rights = $this->fetchSimulatedRights();
        }

        $this->log(sprintf('User [%s] | Fetched [%s] rights.', $userID, count($rights)));
        $this->logData($rights);

        $this->setValue(self::KEY_NAME_USER_ID, $userID);
        $this->setValue(self::KEY_NAME_USER_RIGHTS, implode(',', $rights));

        $this->log(sprintf('User [%s] | Stored in the session.', $userID));

        // Unpack the user, as processes after this may need to access
        // the instance. The redirect, for example, needs to know whether
        // the user is a developer.
        $unpacked = $this->unpackUser();

        $this->triggerUserAuthenticated($unpacked);

        $this->log('User [%s] | Redirecting to the initially requested URL.', $userID);

        if(!isCLI()) {
            Application::redirect($this->unpackTargetURL());
        }
    }

    private function initRightPresets() : void
    {
        $user = Application::createSystemUser();

        if(!$user instanceof Application_User_Extended)
        {
            return;
        }

        $roles = $user->getRightsManager()->getRoles();

        foreach($roles as  $role)
        {
            $this->rightPresets[$role->getID()] = $role->getRightIDs();
        }
    }

    /**
     * Initializes the session when in simulation mode. Has no
     * effect if simulation mode is disabled.
     *
     * @param int $userID The currently authenticated user's ID
     * @throws Application_Exception
     */
    private function initSimulatedSession(int $userID) : int
    {
        // Ignore this if we are not in simulation mode.
        if (!Application::isSessionSimulated()) {
            return $userID;
        }

        $this->log('Session is in simulated mode.');

        // Store the name of the rights preset we want to use.
        if (isset($_REQUEST[self::KEY_NAME_RIGHTS_PRESET]) && array_key_exists($_REQUEST[self::KEY_NAME_RIGHTS_PRESET], $this->rightPresets))
        {
            $presetName = $_REQUEST[self::KEY_NAME_RIGHTS_PRESET];

            $this->log(sprintf('Selected rights preset [%s].', $presetName));

            $this->setValue(self::KEY_NAME_RIGHTS_PRESET, $presetName);
        }

        $simulateID = $this->getSimulatedUserID();

        // Switch the simulated user?
        if($simulateID !== $userID)
        {
            $this->log(sprintf('Using the user ID [%s] for the simulated session.', $simulateID));

            $this->storeUser(AppFactory::createUsers()->getByID($simulateID));
        }

        return $simulateID;
    }

    private function getSimulatedUserID() : int
    {
        $simulateID = (int)$this->getValue(self::KEY_NAME_SIMULATED_ID, $this->defaultSimulatedUser);

        // A user has been selected in the request.
        if(isset($_REQUEST[self::KEY_NAME_SIMULATED_ID], $this->simulateableUsers[$_REQUEST[self::KEY_NAME_SIMULATED_ID]]))
        {
            $simulateID = (int)$_REQUEST[self::KEY_NAME_SIMULATED_ID];
        }

        return $simulateID;
    }

    private function unpackRights() : array
    {
        if(Application::isSessionSimulated())
        {
            return $this->fetchSimulatedRights();
        }

        return ConvertHelper::explodeTrim(',', $this->getRightsString());
    }

    /**
     * Determines which URL to redirect to after authentication.
     *
     * The session automatically stores the URL accessed originally
     * before the authentication was triggered, and redirects to
     * that URL after authentication.
     *
     * @return string
     * @see self::storeUser()
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
     * @return array<string,array<int,string>>
     * @throws Application_Exception
     */
    public function getRightPresets() : array
    {
        $this->requireSimulatedSession();

        if (empty($this->rightPresets)) {
            throw new Application_Exception(
                'No right presets defined',
                'You have to define the available right presets in your session class.',
                self::ERROR_NO_RIGHT_PRESETS_PRESENT
            );
        }

        return $this->rightPresets;
    }

    public function getRightPreset() : string
    {
        if (!$this->presetExists(self::ADMIN_PRESET_ID))
        {
            throw new Application_Exception(
                'Admin rights preset not defined',
                sprintf(
                    'You have to define the [%s] rights preset in the [%s] class for the simulated session mode to work.',
                    self::ADMIN_PRESET_ID,
                    get_class($this)
                ),
                self::ERROR_ADMIN_RIGHTS_PRESET_MISSING
            );
        }

        $select = self::ADMIN_PRESET_ID;

        $sessionPreset = $this->getPresetBySession();
        if(!empty($sessionPreset))
        {
            $select = $sessionPreset;
        }

        $requestPreset = $this->getPresetByRequest();
        if (!empty($requestPreset))
        {
            $select = $requestPreset;
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
        if(isset($_REQUEST[self::KEY_NAME_RIGHTS_PRESET]) && $this->presetExists($_REQUEST[self::KEY_NAME_RIGHTS_PRESET]))
        {
            return $_REQUEST[self::KEY_NAME_RIGHTS_PRESET];
        }

        return '';
    }

    public function presetExists(string $presetID) : bool
    {
        $presets = $this->getRightPresets();

        return isset($presets[$presetID]);
    }

    private function requireSimulatedSession() : void
    {
        if (Application::isSessionSimulated())
        {
            return;
        }

        throw new Application_Exception(
            'Only available during simulated session mode.',
            '',
            self::ERROR_ONLY_FOR_SIMULATED_SESSION
        );
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
        $this->requireSimulatedSession();

        $presetID = $this->getRightPreset();

        if(isset($this->rightPresets[$presetID])) {
            return $this->rightPresets[$presetID];
        }

        throw new Application_Session_Exception(
            'Right preset not defined',
            sprintf(
                'The right preset [%s] does not exist in class [%s].',
                $presetID,
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

    final public function authenticate() : Application_User
    {
        if(isset($this->user))
        {
            if (isset($_REQUEST['logout']) && string2bool($_REQUEST['logout']) === true)
            {
                $this->logOut();
            }

            return $this->user;
        }

        $this->initRightPresets();

        $userID = $this->initSimulatedSession($this->getUserID());

        // Starts the authentication process. This ends either by
        // storing the user ID in the session via `storeUser()`,
        // or via a redirect to an error page.
        if ($userID === 0)
        {
            $this->log('No user ID stored, starting authentication.');

            $this->initAuthentication();
        }

        $this->log(sprintf('User ID [%s] found, initializing session.', $this->getUserID()));

        $this->user = $this->unpackUser();

        return $this->user;
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
