<?php
/**
 * File containing the {@see Application_Session_Base} class.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session_Base
 */

declare(strict_types=1);

/**
 * Base session class: defines the core mechanisms of the
 * available session systems. Also included in the mechanism
 * is triggering the authentication, and storing the user
 * information in the session.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Session_Base implements Application_Session
{
    use Application_Traits_Loggable;

    public const ERROR_ADMIN_RIGHTS_PRESET_MISSING = 22201;
    public const ERROR_ONLY_FOR_SIMULATED_SESSION = 22202;
    public const ERROR_NO_RIGHT_PRESETS_PRESENT = 22203;
    public const ERROR_AUTH_DID_NOT_RETURN_USER = 22205;
    public const ERROR_INVALID_USER_CLASS = 22206;
    public const ERROR_INVALID_USER_ID = 22207;

    public const KEY_NAME_USER_ID = 'user_id';
    public const KEY_NAME_USER_RIGHTS = 'user_rights';
    public const KEY_NAME_SIMULATED_ID = 'simulate_user_id';
    public const KEY_NAME_RIGHTS_PRESET = 'simulate_rights_preset';

    public const ADMIN_PRESET_ID = 'Admin';

    public const LOGOUT_REASON_USER_REQUEST = 75901;
    public const LOGOUT_REASON_LOGIN_NOT_ALLOWED = 75902;

    /**
     * @var array<int,string>
     */
    protected $simulateableUsers = array(
        Application::USER_ID_SYSTEM => 'System',
        Application::USER_ID_DUMMY => 'Dummy'
    );

    /**
     * @var int
     */
    protected $defaultSimulatedUser = Application::USER_ID_SYSTEM;

    /**
     * @var array<string,string|array<int,string>>
     */
    protected $rightPresets = array();

    /**
     * @var Application_User|null
     */
    protected $user;

    abstract protected function start() : void;
    abstract protected function handleLogout() : void;

    /**
     * Handles the logic to log in the user. This must
     * call `storeUser()` when a user has been successfully
     * logged in.
     *
     * It should also handle registering new users, if
     * this is applicable.
     */
    abstract protected function handleLogin() : ?Application_Users_User;

    public function __construct()
    {
        $this->init();
    }

    final protected function init() : void
    {
        $this->log('Starting the session.');

        $this->start();

        $this->log('Session started with ID [%s].', $this->getID());

        $this->initRightPresets();

        if (!Application::isAuthenticationEnabled())
        {
            $this->log('Authentication is disabled, using the system user.');
            $this->user = Application::createSystemUser();
            return;
        }

        if (isset($_REQUEST['logout']) && $_REQUEST['logout'] === 'yes')
        {
            $this->logOut();
        }

        $userID = $this->getUserID();

        $this->initSimulatedSession($userID);

        // Starts the authentication process. This ends either by
        // storing the user ID in the session via `storeUser()`,
        // or via a redirect to an error page.
        if ($userID === 0)
        {
            $this->log('No user ID stored, starting authentication.');

            $this->initAuthentication();
        }
        
        $this->log(sprintf('User ID [%s] found, initializing session.', $this->getUserID()));

        // A user is present in the session and can be unpacked.
        $this->user = $this->unpackUser();

        if ($this->user !== null)
        {
            return;
        }

        throw new Application_Exception(
            'Invalid user credentials',
            sprintf(
                'Could not create User for ID [%s] as stored in the session.',
                $this->getUserID()
            ),
            self::ERROR_INVALID_USER_ID
        );
    }

    public function logOut(int $reasonID=self::LOGOUT_REASON_USER_REQUEST): void
    {
        $this->log('Logout requested, logging the user out.');

        $this->handleLogout();

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

    protected function initAuthentication() : void
    {
        $this->log('No user ID found in the session, initiating login sequence.');

        $user = $this->handleLogin();

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
     * Returns an application driver specific object, e.g. `DriverName_User`.
     *
     * @return Application_User
     * @throws Application_Exception
     */
    protected function unpackUser() : Application_User
    {
        $userClass = Application::getUserClass();
        $userID = $this->getUserID();

        $user = Application::createUser($userID);
        $user->setRights($this->unpackRights());

        return $user;
    }

    /**
     * Stores the specified user in the session as the logged in user
     * after authentication.
     *
     * @param Application_Users_User $user
     */
    protected function storeUser(Application_Users_User $user) : void
    {
        $userID = $user->getID();

        $this->log(sprintf('User [%s] | Authentication successful.', $userID));

        if(!Application::isSessionSimulated())
        {
            $rights = $this->fetchRights($user);
        }
        else
        {
            $rights = explode(',', $this->getCurrentRights());
        }

        $this->log(sprintf('User [%s] | Fetched [%s] rights.', $userID, count($rights)));
        $this->logData($rights);

        $this->setValue(self::KEY_NAME_USER_ID, $userID);
        $this->setValue(self::KEY_NAME_USER_RIGHTS, implode(',', $rights));

        $this->log(sprintf('User [%s] | Stored in the session.', $userID));

        Application::redirect($this->unpackTargetURL());
    }

    protected function initRightPresets() : void
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
     * @param int $userID The currently logged in user ID
     * @throws Application_Exception
     */
    protected function initSimulatedSession(int $userID) : void
    {
        // Ignore this if we are not in simulation mode.
        if (!Application::isSessionSimulated()) {
            return;
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

            $this->storeUser(Application_Driver::createUsers()->getByID($simulateID));
        }
    }

    private function getSimulatedUserID() : int
    {
        $simulateID = $this->defaultSimulatedUser;

        // Is a user already simulated in the session?
        if(isset($_SESSION[self::KEY_NAME_SIMULATED_ID], $this->simulateableUsers[$_SESSION[self::KEY_NAME_SIMULATED_ID]]))
        {
            $simulateID = (int)$_SESSION[self::KEY_NAME_SIMULATED_ID];
        }

        // A user has been selected in the request.
        if(isset($_REQUEST[self::KEY_NAME_SIMULATED_ID], $this->simulateableUsers[$_REQUEST[self::KEY_NAME_SIMULATED_ID]]))
        {
            $simulateID = (int)$_REQUEST[self::KEY_NAME_SIMULATED_ID];
        }

        return $simulateID;
    }

    protected function unpackRights() : array
    {
        $rights = (string)$this->getValue(self::KEY_NAME_USER_RIGHTS);

        if(Application::isSessionSimulated())
        {
            $rights = $this->getCurrentRights();
        }

        return explode(',', $rights);
    }

    protected function unpackTargetURL() : string
    {
        return APP_URL;
    }

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
        if(isset($_SESSION[self::KEY_NAME_RIGHTS_PRESET]) && $this->presetExists($_SESSION[self::KEY_NAME_RIGHTS_PRESET]))
        {
            return $_SESSION[self::KEY_NAME_RIGHTS_PRESET];
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

    public function getCurrentRights() : string
    {
        $this->requireSimulatedSession();

        $rights = $this->rightPresets[$this->getRightPreset()];

        if(is_array($rights)) {
            return implode(',', $rights);
        }

        return $rights;
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

        $users = Application_Driver::createUsers();
        $user = $users->getByEmail($email);

        if($user !== null)
        {
            $this->log(sprintf(
                'User [%s] | User already exists in the database.',
                $email
            ));

            return $this->updateUser($user, $firstname, $lastname, $foreignID);
        }

        // User not found in DB, and registration is disabled: redirect
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

        $users = Application_Driver::createUsers();

        DBHelper::startTransaction();
        $user = $users->createNewUser($email, $firstname, $lastname, $foreignID);
        DBHelper::commitTransaction();

        return $user;
    }

    protected function updateUser(Application_Users_User $user, string $firstname, string $lastname, string $foreignID) : Application_Users_User
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
}
