<?php
/**
 * File containing the {@link Application_Session_Native} class.
 *
 * @package Application
 * @subpackage Core
 * @see Application_Session_Native
 */

/**
 * Session implementation using the native PHP session functions.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Session_Native extends Application_Session_Base
{
    protected function start(): void
    {
        session_start();
    }

    protected function handleLogout() : void
    {
        $_SESSION = array();
        session_destroy();
    }

    public function getValue($name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    public function setValue($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function valueExists($name)
    {
        return isset($_SESSION[$name]);
    }

    public function unsetValue($name)
    {
        if ($this->valueExists($name)) {
            unset($_SESSION[$name]);
        }
    }
}
