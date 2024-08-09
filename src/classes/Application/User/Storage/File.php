<?php
/**
 * File containing the {@link Application_User_Storage_File} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage_File
 */

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * File storage for users: saves all user-related data to a JSON
 * file in the application's <code>storage/userdata</code> folder.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_User_Storage_File extends Application_User_Storage
{
    /**
     * @var string
     */
    private $dataFile;

    /**
     * @var array<string,string>
     */
    private $data = array();

    /**
     * @var bool
     */
    private $loaded = false;

    protected function init() : void
    {
        $this->dataFile = Application::getStorageSubfolderPath('userdata').'/'.$this->userID.'.json';
    }

    /**
     * @return array<string,string>
     * @throws FileHelper_Exception
     */
    public function load() : array
    {
        if($this->loaded)
        {
            return $this->data;
        }

        $this->loaded = true;

        if(file_exists($this->dataFile))
        {
            $this->data = FileHelper::parseJSONFile($this->dataFile);
        }

        return $this->data;
    }

    /**
     * @throws FileHelper_Exception
     */
    public function reset(?string $prefix=null) : void
    {
        if($prefix !== null) {
            $this->resetByPrefix($prefix);
            return;
        }

        if(file_exists($this->dataFile)) {
            FileHelper::deleteFile($this->dataFile);
        }
        
        $this->data = array();
    }

    private function resetByPrefix(string $prefix) : void
    {
        $keys = array_keys($this->data);

        foreach($keys as $key) {
            if(strpos($key, $prefix) === 0) {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * @param array<string,string> $data
     */
    public function save(array $data) : void
    {
        $this->data = array_merge($this->data, $data);

        $this->dumpToDisk();
    }
    
    public function removeKey(string $name) : void
    {
        if(!isset($this->data[$name]))
        {
            return;
        }
        
        unset($this->data[$name]);

        $this->dumpToDisk();       
    }
    
    protected function dumpToDisk() : void
    {
        FileHelper::saveAsJSON($this->data, $this->dataFile);
    }
}
