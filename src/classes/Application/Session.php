<?php
/**
 * File containing the {@link Application_Session} interface.
 *
 * @package Application
 * @subpackage Core
 * @see Application_Session
 */

/**
 * Interface for application session handling classes.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_Session extends Application_Interfaces_Loggable
{
    /**
     * The user object that is returned must implement the Application_User interface.
     * @return Application_User|null
     */
    public function getUser();

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
     * @param mixed $default
     * @return mixed
     */
    public function getValue($name, $default = null);

    /**
     * Sets a session value.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setValue($name, $value);

    /**
     * Removes / unsets a session value. Has no effect
     * if the value does not exist to begin with.
     *
     * @param string $name
     */
    public function unsetValue($name);

    /**
     * Checks whether the specified session value exists / is set.
     *
     * @param string $name
     */
    public function valueExists($name);

    /**
     * Retrieves the name of the currently active simulated
     * session rights preset.
     *
     * @return string
     */
    public function getRightPreset();

    /**
     * Retrieves the currently active simulated session rights preset.
     * By default, this is the Admin rights list.
     *
     * Returns a string with role names, separated with commas.
     *
     * Example:
     *
     * AddRecord,DeleteRecord,PublishRecord
     *
     * @return string
     */
    public function getCurrentRights() : string;

    /**
     * Retrieves a list of all available right presets, as an associative
     * array with preset name => roles string pairs.
     *
     * Example:
     *
     * array(
     *     'Admin' => 'AddRecord,DeleteRecord,PublishRecord',
     *     'Reader' => 'ViewRecord'
     * )
     *
     * @return array
     */
    public function getRightPresets();

    /**
     * @param int $reasonID
     */
    public function logOut(int $reasonID=0) : void;
}
