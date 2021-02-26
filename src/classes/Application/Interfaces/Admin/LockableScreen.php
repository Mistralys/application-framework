<?php

/**
 * File containing the {@see Application_Admin_ScreenInterface} interface.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_ScreenInterface
 */

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
interface Application_Interfaces_Admin_LockableScreen extends Application_Admin_ScreenInterface
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
    
    public function getLockManagerPrimary();
    
   /**
    * @return string
    */
    public function getLockLabel();
}
