<?php

require_once 'Application/LookupItems/Item.php';

class Application_LookupItems
{
    const ERROR_INVALID_LOOKUP_ITEM_CLASS = 25101;
    
    protected $driverID;
    
    protected $folder;
    
    public function __construct(Application_Driver $driver)
    {
        $this->driverID = $driver->getID();
        
        $this->folder = sprintf(
            '%s/assets/classes/%s/LookupItems',
            APP_ROOT,
            $this->driverID
        );
    }
    
    protected $items;
    
    public function getItems()
    {
        if(isset($this->items)) {
            return $this->items;
        }
        
        $items = array();
        
        if(!is_dir($this->folder)) {
            $this->log(sprintf('The driver has no lookup items folder, which is expected at [%s].', $this->folder));
            return $items;
        }
        
        $names = AppUtils\FileHelper::createFileFinder($this->folder)
        ->getPHPClassNames();
        
        foreach($names as $name) 
        {
            $this->log(sprintf('Adding item [%s].', $name));
            
            $className = sprintf(
                '%s_LookupItems_%s',
                $this->driverID,
                $name
            );
            
            Application::requireClass($className);
            
            $item = new $className();
            if(!$item instanceof Application_LookupItems_Item) {
                throw new Application_Exception(
                    'Invalid lookup item',
                    sprintf(
                        'The lookup item class [%s] does not extend the [%s] base class.',
                        $className,
                        'Application_LookupItems_Item'
                    ),
                    self::ERROR_INVALID_LOOKUP_ITEM_CLASS
                );
            }
            
            $items[] = $item;
        }
 
        $this->items = $items;
        
        return $items;
    }
    
    protected function log($message)
    {
        Application::log(
            'Lookup items | '.$message    
        );
    }
}
