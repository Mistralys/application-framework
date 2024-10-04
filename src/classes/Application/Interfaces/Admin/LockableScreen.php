<?php
/**
 * @package Application
 * @subpackage Admin
 */

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * Interface for administration screens: defines all methods
 * that administration screens have to share beyond what the
 * skeleton offers.
 *
 * NOTE: This is mostly implemented in the matching trait.
 *
 * WARNING: The interface is not type hinted for backwards
 * compatibility.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Screen
 * @see Application_Admin_Skeleton
 */
interface Application_Interfaces_Admin_LockableScreen extends AdminScreenInterface
{
   /**
    * @return bool
    */
    public function isLocked();

   /**
    * @return bool
    */
    public function isLockable(); 
    
   /**
    * @return string
    */
    public function getLockMode();
    
    public function getLockManager() : ?Application_LockManager;

    /**
     * Like {@see Application_Interfaces_Admin_LockableScreen::getLockManager()},
     * but throws an exception if the manager instance is not set.
     *
     * @return Application_LockManager
     * @throws Application_Exception
     */
    public function requireLockManager() : Application_LockManager;

    public function getLockManagerPrimary();
    
   /**
    * @return string
    */
    public function getLockLabel();
}
