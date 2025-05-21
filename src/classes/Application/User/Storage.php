<?php
/**
 * File containing the {@link Application_User_Storage} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage
 */

declare(strict_types=1);

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
    
    protected function init() : void
    {
        
    }

    /**
     * @return array<string,string>
     */
    abstract public function load() : array;

    /**
     * Resets all settings, or a set of settings when
     * a prefix is provided.
     *
     * @param string|null $prefix
     * @return void
     */
    abstract public function reset(?string $prefix=null) : void;

    /**
     * @param array<string,string> $data
     */
    abstract public function save(array $data) : void;
    
    abstract public function removeKey(string $name) : void;
}
