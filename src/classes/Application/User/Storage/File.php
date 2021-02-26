<?php
/**
 * File containing the {@link Application_User_Storage_File} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage_File
 */

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
    protected $dataFile;
    
    protected $data;
    
    protected function init()
    {
        $this->dataFile = Application::getStorageSubfolderPath('userdata').'/'.$this->userID.'.json';
    }
    
    public function load()
    {
        if(!isset($this->data)) 
        {
            $this->data = array();
            
            if(file_exists($this->dataFile)) {
                $this->data = AppUtils\FileHelper::parseJSONFile($this->dataFile);
            }
        }
        
        return $this->data;
    }
    
    public function reset()
    {
        if(file_exists($this->dataFile)) 
        {
            unlink($this->dataFile);
        }
        
        $this->data = null;
    }
    
    public function save($data)
    {
        $this->data = array_merge($this->data, $data);
        $this->dumpToDisk();
    }
    
    public function removeKey($name)
    {
        if(!isset($this->data[$name])) {
            return;
        }
        
        unset($this->data[$name]);
        $this->dumpToDisk();       
    }
    
    protected function dumpToDisk()
    {
        AppUtils\FileHelper::saveAsJSON($this->data, $this->dataFile);
    }
}