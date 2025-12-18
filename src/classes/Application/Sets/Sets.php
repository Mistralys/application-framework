<?php
/**
 * @package Application
 * @subpackage Sets
 */

use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Sets\AppSetsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper_Exception;

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
    public const string DEFAULT_ID = '__default';

    protected static ?Application_Sets $instance = null;
    
   /**
    * @var Application_Sets_Set[]
    */
    protected array $sets = array();
    
    protected string $configPath;
 
   /**
    * @return Application_Sets
    */
    public static function getInstance() : Application_Sets
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

        $this->load();
    }

    /**
    * @return Application_Sets_Set[]
    */
    public function getSets() : array
    {
        return $this->sets;
    }

    /**
     * @return void
     *
     * @throws AppSetsException
     * @throws JsonException
     * @throws FileHelper_Exception
     */
    protected function load() : void
    {
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
    
    public function getAdminListURL(array $params=array()) : string
    {
        $params['submode'] = 'list';
        return $this->getAdminURL($params);
    }
    
    public function getAdminCreateURL(array $params=array()) : string
    {
        $params['submode'] = 'create';
        return $this->getAdminURL($params);
    }
    
    protected function getAdminURL(array $params=array()) : string
    {
        $params['page'] = 'devel';
        $params['mode'] = 'appsets';

        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }
    
   /**
    * Creates a new application set and returns the instance.
    * 
    * @param string $id
    * @param AdminAreaInterface $defaultArea
    * @param AdminAreaInterface[] $enabledAreas
    * @return Application_Sets_Set
    */
    public function createNew(string $id, AdminAreaInterface $defaultArea, array $enabledAreas=array()) : Application_Sets_Set
    {
        if($this->idExists($id)) {
            throw new AppSetsException(
                'Application set ID already exists',
                sprintf(
                    'Cannot create a new set with ID [%s], this ID is already in use by an existing application set.',
                    $id
                ),
                AppSetsException::ERROR_SET_ID_ALREADY_EXISTS
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
    public function idExists(string $id) : bool
    {
        if($id === self::DEFAULT_ID) {
            return true;
        }
        
        return isset($this->sets[$id]);
    }
    
    protected ?Application_Sets_Set $default = null;

    /**
     * Retrieves a set by its ID.
     * @param string $id
     * @return Application_Sets_Set
     *
     * @throws AppSetsException
     */
    public function getByID(string $id) : Application_Sets_Set
    {
        if(isset($this->sets[$id])) {
            return $this->sets[$id];
        }
        
        if($id === self::DEFAULT_ID)
        {
            return $this->getDefault();
        }
        
        throw new AppSetsException(
           'Unknown application set',
           sprintf(
               'The application set [%s] does not exist. Always check beforehand with idExists to avoid this exception.',
               $id
           ),
            AppSetsException::ERROR_UNKNOWN_SET
        );
    }

    public function getDefault() : Application_Sets_Set
    {
        if(!isset($this->default)) {
            $driver = Application_Driver::getInstance();
            $this->default = new Application_Sets_Set(self::DEFAULT_ID, $driver->createArea('Settings'));
            $this->default->enableAreas($driver->getAdminAreaObjects());
        }

        return $this->default;
    }

    /**
     * Saves all application sets to the configuration file.
     *
     * @throws AppSetsException
     */
    public function save() : void
    {
        $data = array();
        foreach($this->sets as $set) {
            $data[$set->getID()] = $set->toArray();
        }

        try
        {
            JSONFile::factory($this->configPath)->putData($data, true);
        }
        catch (Throwable $e)
        {
            throw new AppSetsException(
                'Cannot save the application sets configuration',
                sprintf(
                    'Tried saving to the file [%s].',
                    $this->configPath
                ),
                AppSetsException::ERROR_CANNOT_SAVE_CONFIGURATION,
                $e
            );
        }
    }
    
    public function deleteSet(Application_Sets_Set $set) : void
    {
        $setID = $set->getID();

        if(isset($this->sets[$setID]))
        {
            unset($this->sets[$setID]);
        }
    }
    
   /**
    * Renames the ID of a set. Called by a set when
    * it is renamed, do not call this manually.
    * 
    * @param Application_Sets_Set $set
    * @param string $newID
    * @throws AppSetsException
    */
    public function handle_renameSet(Application_Sets_Set $set, string $newID) : void
    {
        $oldID = $set->getID();
        
        if(!isset($this->sets[$oldID])) {
            throw new AppSetsException(
                'Cannot rename set that does not exist',
                sprintf(
                    'The set [%s] does not exist, and cannot be renamed to [%s].',
                    $oldID,
                    $newID
                ),
                AppSetsException::ERROR_CANNOT_RENAME_INEXISTANT_SET
            );
        }
        
        if(isset($this->sets[$newID])) {
            throw new AppSetsException(
                'Cannot rename set, same name already exists',
                sprintf(
                    'Cannot rename set [%s] to [%s], that set already exists.',
                    $oldID,
                    $newID
                ),
                AppSetsException::ERROR_CANNOT_RENAME_TO_EXISTING_NAME
            );
        }
        
        unset($this->sets[$set->getID()]);

        $this->sets[$newID] = $set;
    }
}