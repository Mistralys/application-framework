<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin;

use Application_Admin_ScreenInterface;
use Application_User;

/**
 * Utility abstract class to implement a class that can access
 * the required rights for admin screens.
 *
 * <h2>Usage</h2>
 *
 * 1. Create a new class that extends this one.
 * 2. Implement the abstract method `_registerRights()`.
 * 3. Use the `register()` method to register the rights for each screen.
 *
 * <h2>Recommended</h2>
 *
 * 1. Use a separate enum class to set screen rights.
 * 2. Define a constant that associates the screen classes with their rights.
 * 3. Iterate over the constant to register the screens.
 *
 * @package Application
 * @subpackage Admin
 */
abstract class BaseScreenRights
{
    private static array $rights = array();

    /**
     * @param Application_Admin_ScreenInterface|class-string $screen
     * @return string
     */
    public function getByClass($screen) : string
    {
        if($screen instanceof Application_Admin_ScreenInterface) {
            $screenClass = get_class($screen);
        } else {
            $screenClass = $screen;
        }

        return self::$rights[$screenClass] ?? Application_User::RIGHT_DEVELOPER;
    }

    public function __construct()
    {
        $this->registerRights();
    }

    private static bool $rightsRegistered = false;

    protected function registerRights() : void
    {
        if(self::$rightsRegistered) {
            return;
        }

        self::$rightsRegistered = true;

        $this->_registerRights();
    }

    abstract protected function _registerRights() : void;

    /**
     * @param class-string $screenClass
     * @param string $right
     * @return $this
     */
    protected function register(string $screenClass, string $right) : self
    {
        self::$rights[$screenClass] = $right;
        return $this;
    }
}
