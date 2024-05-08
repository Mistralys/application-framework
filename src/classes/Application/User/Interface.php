<?php
/**
 * File containing the {@link Application_User_Interface} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Interface
 */

/**
 * Interface for classes implementing an application user.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_User_Interface
{
    public function getID() : int;
    
    public function getEmail() : string;
    
    public function getFirstname() : string;
    
    public function getLastname() : string;
    
    public function getName() : string;
    
    public function hasRight(string $rightName) : bool;
    public function rightExists(string $rightName) : bool;
    
    public function saveSettings() : void;
    
    public function loadSettings() : void;
    
    public function isDeveloper() : bool;

    public function canTranslateUI() : bool;
    public function canLogin() : bool;
    
    public function getRoleGroups() : array;
    
    public function getGrantableRoles() : array;
    
    public function can(string $rightName) : bool;
    
    public function setSetting(string $name, string $value) : bool;
    public function getSetting(string $name, string $default='');

    public function getArraySetting(string $name, array $default=array()) : array;
    public function setArraySetting(string $name, array $value) : void;

    public function setIntSetting(string $name, int $value) : void;
    public function getIntSetting(string $name, int $default=0) : int;

    public function setBoolSetting(string $name, bool $value) : void;
    public function getBoolSetting(string $name, bool $default=false) : bool;

    public function resetSettings() : void;
    
    public function removeSetting(string $name) : void;
}
