<?php
/**
 * File containing the {@link Application_Session} interface.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session
 */

declare(strict_types=1);

use Application\Application;
use Application\EventHandler\Eventables\EventableInterface;
use Application\EventHandler\Eventables\EventableListener;

/**
 * Interface for application session handling classes.
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
interface Application_Session extends EventableInterface
{
    public function getID() : string;

    /**
     * Name used to identify the session, and keep values
     * separate between different scripts and applications.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Retrieves the type of authentication the session uses, e.g. {@see Application_Session_AuthTypes_NoneInterface::TYPE_ID}.
     * @return string
     */
    public function getAuthTypeID() : string;

    /**
     * Fetches the currently authenticated user. If this is empty,
     * the authentication has not been performed yet.
     *
     * @return Application_User|null
     */
    public function getUser() : ?Application_User;

    /**
     * Starts the user authentication process.
     *
     * This must only be called once. Use {@see Application::isUserReady()}
     * to check if a user has already been authenticated.
     *
     * NOTE: In the usual workflow of the application,
     * this method is called automatically by the bootstrap
     * for the current screen, see {@see Application_Bootstrap_Screen::authenticateUser()}.
     *
     * @return Application_User
     */
    public function authenticate() : Application_User;

    public function isStarted() : bool;

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
     * @param Application_User $user
     * @return string[]
     */
    public function fetchRights(Application_User $user) : array;

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

    /**
     * Gets the name of the currently active simulated
     * session rights preset.
     *
     * @return string The preset name or an empty string if none.
     */
    public function getPresetBySession() : string;

    public function getRightsString() : string;

    /**
     * Retrieves the currently active simulated session rights preset.
     * By default, this is the Admin rights list.
     *
     * @return string[] List of right names.
     */
    public function fetchSimulatedRights() : array;

    /**
     * Retrieves a list of all available right role presets.
     *
     * @return Application_User_Rights_Role[]
     */
    public function getRightPresets() : array;

    /**
     * @param int $reasonID
     * @return void|never Will exit the application if redirects are enabled.
     * @see Application_Session_Base::setRedirectsEnabled()
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
     * @return EventableListener
     */
    public function onUserAuthenticated(callable $callback) : EventableListener;

    /**
     * Adds a listener for the {@see self::EVENT_BEFORE_LOG_OUT} event,
     * which is called right before the user is logged out.
     *
     * The callback gets a single parameter:
     *
     * 1) The event instance, {@see BeforeLogOutEvent}.
     *
     * @param callable $callback
     * @return EventableListener
     */
    public function onBeforeLogOut(callable $callback) : EventableListener;

    /**
     * Adds a listener for the {@see self::EVENT_STARTED} event,
     * which is called when the session has been started.
     *
     * The callback gets a single parameter:
     *
     * 1) The event instance, {@see SessionStartedEvent}.
     *
     * @param callable $callback
     * @return EventableListener
     */
    public function onSessionStarted(callable $callback) : EventableListener;

    /**
     * Starts the session. It is then available, but no user has yet
     * been authenticated. For this, the {@see self::authenticate()} method
     * must be called.
     *
     * @return $this
     */
    public function start() : self;

    /**
     * Destroys the session.
     * @return self
     */
    public function destroy() : self;
}
