<?php

class Application_Driver_Storage_File extends Application_Driver_Storage
{
    protected $dataFile;
    
    protected $data;
    
    protected function init()
    {
        $this->dataFile = Application::getStorageSubfolderPath('settings').'/driver.json';
        
        // to avoid multiple writes for single setting values,
        // we use the shutdown event to save everything at the
        // end of the request.
        Application_EventHandler::addListener('SystemShutDown', array($this, 'handle_shutDown'));
    }
    
    public function get($name)
    {
        $this->load();
        
        if(isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        return null;
    }
    
    public function set($name, $value, $role)
    {
        $this->load();
        
        $this->data[$name] = $value;
    }

    public function setExpiry($name, $date)
    {
        $this->load();

        $this->data[$name] =$date->format('Y-m-d H:i:s');
    }
    
    public function delete($name)
    {
        $this->load();
        
        if(isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }
    
    protected function load()
    {
        if(isset($this->data)) {
            return;
        }
        
        $this->data = array();
        
        if(file_exists($this->dataFile)) {
            $this->data = AppUtils\FileHelper::parseJSONFile($this->dataFile);
        }
    }
    
    public function handle_shutDown(Application_EventHandler_Event_SystemShutDown $event)
    {
        $this->writeToDisk();
    }
    
    protected function writeToDisk()
    {
        $this->load();
        
        AppUtils\FileHelper::saveAsJSON($this->data, $this->dataFile);
    }
}