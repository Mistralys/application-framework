<?php
/**
 * File containing the {@see DBHelper_Traits_LooseRecord} trait.
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @see DBHelper_Traits_LooseRecord
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;

/**
 * Trait for working with database records, independently of a
 * full-fledged DB records collection. 
 * 
 * Use this as a drop-in to load data from the database by ID,
 * with everything required to access and update the data.
 * 
 * Usage:
 * 
 * - Use the trait: `use DBHelper_Traits_LooseRecord`
 * - Add the interface: `implements DBHelper_Interface_LooseRecord`
 * 
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see DBHelper_Interface_LooseRecord
 */
trait DBHelper_Traits_LooseRecord
{
   /**
    * @var array<string,string>
    */
    protected $recordData;
    
   /**
    * @var integer
    */
    protected $recordID;
    
   /**
    * @var string
    */
    protected $recordTable;
    
   /**
    * @var string
    */
    protected $recordPrimary;
    
   /**
    * @var boolean
    */
    protected $recordModified = false;
    
   /**
    * @var string[]
    */
    protected $recordKeyNames;
    
    public function __construct(int $recordID)
    {
        $this->recordID = $recordID;
        $this->recordTable = $this->getRecordTable();
        $this->recordPrimary = $this->getRecordPrimaryName();
        
        $this->loadData();
        
        $this->recordKeyNames = array_keys($this->recordData);
        
        $this->init();
    }
    
    private final function loadData() : void
    {
        $this->recordData = DBHelper::createFetchOne($this->recordTable)
        ->whereValue($this->recordPrimary, strval($this->recordID))
        ->fetch();
        
        if(!empty($this->recordData))
        {
            return;
        }
        
        throw new DBHelper_Exception(
            'Could not find record in database.',
            sprintf(
                'Tried fetching record with ID [%s] from table [%s], but it does not exist.',
                $this->recordID,
                $this->recordTable
            ),
            DBHelper_Interface_LooseRecord::ERROR_CANNOT_LOAD_RECORD
        );
    }
    
    abstract protected function init() : void;
    
    abstract public function getRecordTable() : string;
    
    abstract public function getRecordPrimaryName() : string;
    
    public function getID() : int
    {
        return $this->recordID;
    }
    
    public function save() : bool
    {
        if(!$this->isModified())
        {
            return false;
        }
        
        try
        {
            DBHelper::updateDynamic(
                $this->recordTable, 
                $this->recordData, 
                array($this->recordPrimary)
            );
        }
        catch(DBHelper_Exception $e)
        {
            throw new DBHelper_Exception(
                'Could not save record.',
                sprintf(
                    'Tried saving the record with ID [%s] to table [%s], but this failed with an exception. Data set:<br><pre>%s</pre>',
                    $this->recordID,
                    $this->recordTable,
                    json_encode($this->recordData, JSON_PRETTY_PRINT)
                ),
                DBHelper_Interface_LooseRecord::ERROR_COULD_NOT_SAVE_DATA,
                $e
            );
        }
        
        $this->recordModified = false;
        
        return true;
    }
    
    public function isModified() : bool
    {
        return $this->recordModified;
    }
    
    public function getDataKey(string $name) : string
    {
        if(isset($this->recordData[$name]))
        {
            return $this->recordData[$name];
        }
        
        return '';
    }
    
    public function getDataKeyInt(string $name) : int
    {
        return intval($this->getDataKey($name));
    }
    
    public function getDataKeyBool(string $name) : bool
    {
        return ConvertHelper::string2bool($this->getDataKey($name));
    }
    
    public function getDataKeyDate(string $name) : DateTime
    {
        return new DateTime($this->getDataKey($name));
    }
    
    public function setDataKey(string $name, string $value) : bool
    {
        $this->requireValidKey($name);
        
        // Avoid being able to change the record's primary 
        // in the data set.
        if($name === $this->recordPrimary)
        {
            return false;
        }
        
        $old = $this->getDataKey($name);
        
        if($old === $value)
        {
            return false;
        }
        
        $this->recordData[$name] = $value;
        $this->recordModified = true;

        return true;
    }
    
    protected function requireValidKey(string $key) : void
    {
        if(in_array($key, $this->recordKeyNames))
        {
            return;
        }
        
        throw new DBHelper_Exception(
            'Unknown record data key.',
            sprintf(
                'The data key [%s] does not exist in the record from table [%s]. Known keys are: [%s].',
                $key,
                $this->recordTable,
                implode(', ', $this->recordKeyNames)
            ),
            DBHelper_Interface_LooseRecord::ERROR_UNKNOWN_DATA_KEY
        );
    }
}
