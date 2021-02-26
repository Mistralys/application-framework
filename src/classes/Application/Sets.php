<?php
/**
 * File containing the {@see Application_Sets} class.
 *
 * @package Application
 * @subpackage Sets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

use AppUtils\FileHelper;

/**
 * Helper class used to manage application sets: these
 * can be used to create different application UI 
 * environments with only specific administration areas.
 * 
 * @package Application
 * @subpackage Sets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Sets
{
    const ERROR_SET_ID_ALREADY_EXISTS = 12701;
    const ERROR_UNKNOWN_SET = 12702;
    const ERROR_CANNOT_SAVE_CONFIGURATION = 12703;
    const ERROR_CANNOT_RENAME_INEXISTANT_SET = 12704;
    const ERROR_CANNOT_RENAME_TO_EXISTING_NAME = 12705;
    
   /**
    * @var Application_Sets
    */
    protected static $instance;
    
   /**
    * @var Application_Sets_Set[]
    */
    protected $sets;
    
   /**
    * @var string
    */
    protected $configPath;
 
   /**
    * @return Application_Sets
    */
    public static function getInstance()
    {
        if(!isset(self::$instance)) {
            self::$instance = new Application_Sets();
        }
        
        return self::$instance;
    }
    
    protected function __construct()
    {
        $driver = Application_Driver::getInstance();
        
        $this->configPath = $driver->getConfigFolder().'/appsets.json';
    }
    
   /**
    * @return Application_Sets_Set[]
    */
    public function getSets()
    {
        $this->load();
        
        return $this->sets;
    }
    
    protected function load()
    {
        if(isset($this->sets)) {
           return; 
        }
        
        $this->sets = array();
        
        if(!file_exists($this->configPath)) {
            return;
        }
        
        $data = FileHelper::parseJSONFile($this->configPath);
        
        foreach($data as $setID => $config) 
        {
            $set = Application_Sets_Set::fromArray($config);
            $this->sets[$setID] = $set;
        }
    }
    
    public function getAdminListURL($params=array())
    {
        $params['submode'] = 'list';
        return $this->getAdminURL($params);
    }
    
    public function getAdminCreateURL($params=array())
    {
        $params['submode'] = 'create';
        return $this->getAdminURL($params);
    }
    
    protected function getAdminURL($params=array())
    {
        $params['page'] = 'devel';
        $params['mode'] = 'appsets';
        
        $request = Application_Driver::getInstance()->getRequest();
        return $request->buildURL($params);
    }
    
   /**
    * Creates a new application set and returns the instance.
    * 
    * @param string $id
    * @param Application_Admin_Area $defaultArea
    * @param Application_Admin_Area[] $enabledAreas
    * @return Application_Sets_Set
    */
    public function createNew($id, Application_Admin_Area $defaultArea, $enabledAreas=array())
    {
        $this->load();
        
        if($this->idExists($id)) {
            throw new Application_Exception(
                'Application set ID already exists',
                sprintf(
                    'Cannot create a new set with ID [%s], this ID is already in use by an existing application set.',
                    $id
                ),
                self::ERROR_SET_ID_ALREADY_EXISTS
            );
        }
        
        $set = new Application_Sets_Set($id, $defaultArea);
        
        foreach($enabledAreas as $area) {
            $set->enableArea($area);
        }
        
        $this->sets[$id] = $set;
        
        return $set;
    }
    
   /**
    * Checks whether an application set with this ID exists.
    * @param string $id
    * @return boolean
    */
    public function idExists($id)
    {
        if($id == '__default') {
            return true;
        }
        
        $this->load();
        
        return isset($this->sets[$id]);
    }
    
   /**
    * @var Application_Sets_Set
    */
    protected $default;
    
   /**
    * Retrieves a set by its ID.
    * @param string $id
    * @return Application_Sets_Set|NULL
    */
    public function getByID($id)
    {
        $this->load();
        
        if(isset($this->sets[$id])) {
            return $this->sets[$id];
        }
        
        if($id=='__default') {
            if(!isset($this->default)) {
                $driver = Application_Driver::getInstance();
                $this->default = new Application_Sets_Set('__default', $driver->createArea('Settings'));
                $this->default->enableAreas($driver->getAdminAreaObjects());
            }
            
            return $this->default;
        }
        
        throw new Application_Exception(
           'Unknown application set',
           sprintf(
               'The application set [%s] does not exist. Always check beforehand with idExists to avoid this exception.',
               $id
           ),
           self::ERROR_UNKNOWN_SET
        );
    }
    
   /**
    * Saves all application sets to the configuration file.
    * 
    * @throws Application_Exception
    */
    public function save()
    {
        $this->load();
        
        $data = array();
        foreach($this->sets as $set) {
            $data[$set->getID()] = $set->toArray();
        }
        
        if(!file_put_contents($this->configPath, json_encode($data, JSON_PRETTY_PRINT))) {
            throw new Application_Exception(
                'Cannot save the application sets configuration',
                sprintf(
                    'Tried saving to the file [%s].',
                    $this->configPath
                ),
                self::ERROR_CANNOT_SAVE_CONFIGURATION
            );
        }
    }
    
    public function deleteSet(Application_Sets_Set $set)
    {
        $setID = $set->getID();
        
        if(isset($this->sets[$setID])) {
            unset($this->sets[$setID]);
        }
    }
    
   /**
    * Renames the ID of a set. Called by a set when
    * it is renamed, do not call this manually.
    * 
    * @param Application_Sets_Set $set
    * @param string $newID
    * @throws Application_Exception
    */
    public function handle_renameSet(Application_Sets_Set $set, $newID)   
    {
        $oldID = $set->getID();
        
        if(!isset($this->sets[$oldID])) {
            throw new Application_Exception(
                'Cannot rename set that does not exist',
                sprintf(
                    'The set [%s] does not exist, and cannot be renamed to [%s].',
                    $oldID,
                    $newID
                ),
                self::ERROR_CANNOT_RENAME_INEXISTANT_SET
            );
        }
        
        if(isset($this->sets[$newID])) {
            throw new Application_Exception(
                'Cannot rename set, same name already exists',
                sprintf(
                    'Cannot rename set [%s] to [%s], that set already exists.',
                    $oldID,
                    $newID
                ),
                self::ERROR_CANNOT_RENAME_TO_EXISTING_NAME
            );
        }
        
        unset($this->sets[$set->getID()]);
        $this->sets[$newID] = $set;
    }
}