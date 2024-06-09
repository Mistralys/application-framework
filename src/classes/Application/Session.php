<?php
/**
 * File containing the {@link Application_Session} interface.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session
 */

declare(strict_types=1);

/**
 * Interface for application session handling classes.
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
interface Application_Session extends Application_Interfaces_Eventable
{
    public function getID() : string;

    /**
     * Prefix used to store session values.
     *
     * It is prefixed to session variable names to avoid
     * conflicts with other session variables.
     *
     * @return string
     */
    public function getPrefix() : string;

    /**
     * Fetches the currently authenticated user. If this is empty,
     * the authentication has not been performed yet.
     *
     * @return Application_User|null
     */
    public function getUser() : ?Application_User;

    /**
     * Force the authentication of the user (only done if no user is authenticated yet).
     * @return Application_User
     */
    public function authenticate() : Application_User;

    /**
     * Like {@see self::getUser()}, but triggers the authentication process
     * if no user is authenticated yet.
     *
     * @return Application_User
     * @throws Application_Session_Exception
     */
    public function requireUser() : Application_User;

    /**
     * Fetches a list of all rights available for the specified user.
     *
     * @param Application_Users_User $user
     * @return string[]
     */
    public function fetchRights(Application_Users_User $user) : array;

    /**
     * Whether user registration is enabled.
     * @return bool
     */
    public function isRegistrationEnabled() : bool;

    /**
     * Retrieves a session value, with the possibility
     * to specify the default return value in case it
     * is not set.
     *
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed
     */
    public function getValue(string $name, $default = null);

    /**
     * Sets a session value.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setValue(string $name, $value) : void;

    /**
     * Removes / unsets a session value. Has no effect
     * if the value does not exist to begin with.
     *
     * @param string $name
     */
    public function unsetValue(string $name) : void;

    /**
     * Checks whether the specified session value exists / is set.
     *
     * @param string $name
     */
    public function valueExists(string $name) : bool;

    /**
     * Retrieves the name of the currently active simulated
     * session rights preset.
     *
     * @return string
     */
    public function getRightPreset() : string;

    public function getRightsString() : string;

    /**
     * Retrieves the currently active simulated session rights preset.
     * By default, this is the Admin rights list.
     *
     * @return string[] List of right names.
     */
    public function fetchSimulatedRights() : array;

    /**
     * Retrieves a list of all available right presets, as an associative
     * array with preset name => roles string pairs.
     *
     * Example:
     *
     * array(
     *     'Admin' => array('AddRecord', 'DeleteRecord', 'PublishRecord'),
     *     'Reader' => array('ViewRecord')
     * )
     *
     * @return array<string,array<int,string>>
     */
    public function getRightPresets() : array;

    /**
     * @param int $reasonID
     */
    public function logOut(int $reasonID=0) : void;

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
    public function onUserAuthenticated(callable $callback) : Application_EventHandler_EventableListener;

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
    public function onBeforeLogOut(callable $callback) : Application_EventHandler_EventableListener;

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
    public function onSessionStarted(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * @return $this
     */
    public function start() : self;
}
