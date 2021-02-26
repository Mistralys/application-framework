<?php
/**
 * File containing the {@link Application_User_Storage} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage
 */

/**
 * Base class for user data storage: this defines the API
 * for storage implementations. The user class uses a storage
 * instance to access the user's data. The storage class 
 * handles the actual retrieval and saving of the data.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_User_Storage
{
   /**
    * @var Application_User
    */
    protected $user;
    
   /**
    * @var int
    */
    protected $userID;
    
    public function __construct(Application_User $user)
    {
        $this->user = $user;
        $this->userID = $user->getID();
        
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    abstract public function load();
    
    abstract public function reset();
    
    abstract public function save($data);
    
    abstract public function removeKey($name);
}