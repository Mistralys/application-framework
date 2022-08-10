<?php
/**
 * @package DBHelper
 * @subpackage LooseRecord
 * @see DBHelper_Interface_LooseRecord
 */

declare(strict_types=1);

/**
 * Interface for the {@see DBHelper_Traits_LooseRecord} trait.
 *
 * @package DBHelper
 * @subpackage LooseRecord
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see DBHelper_Traits_LooseRecord
 */
interface DBHelper_Interface_LooseRecord
{
    public const ERROR_CANNOT_LOAD_RECORD = 66701;
    public const ERROR_COULD_NOT_SAVE_DATA = 66702;
    public const ERROR_UNKNOWN_DATA_KEY = 66703;
    
   /**
    * The name of the database table in which the records are stored. 
    * @return string
    */
    public function getRecordTable() : string;
    
   /**
    * Name of the table column in which the primary keys are stored. 
    * @return string
    */
    public function getRecordPrimaryName() : string;
    
   /**
    * The record's ID.
    * @return int
    */
    public function getID() : int;
    
   /**
    * Saves the record to the database, if it has been modified.
    * @return bool
    * @throws DBHelper_Exception
    * 
    * @see DBHelper_Interface_LooseRecord::ERROR_COULD_NOT_SAVE_DATA
    */
    public function save() : bool;
    
   /**
    * Checks whether any changes are pending to be saved.
    * @return bool
    */
    public function isModified() : bool;
    
   /**
    * Retrieves the specified column's value.
    * 
    * NOTE: Trying to retrieve the value of unknown 
    * columns will not throw an error. It will simply
    * return an empty string.
    * 
    * @param string $name
    * @return string
    */
    public function getDataKey(string $name) : string;
    
   /**
    * Retrieves a data key value, and converts it to int.
    * @param string $name
    * @return int
    */
    public function getDataKeyInt(string $name) : int;
    
   /**
    * Retrieves a data key value, and converts it to boolean.
    * 
    * Supported column values are `true`, `false`, `yes`, `no`.
    * 
    * @param string $name
    * @return bool
    */
    public function getDataKeyBool(string $name) : bool;
    
   /**
    * Retrieves a data key value, and converts it to a datetime instance.
    * @param string $name
    * @return DateTime
    */
    public function getDataKeyDate(string $name) : DateTime;
   
   /**
    * Sets a data key value.
    * 
    * NOTE: Will throw an exception if the column does
    * not exist in the record.
    * 
    * @param string $name
    * @param string $value
    * @return bool
    * @throws DBHelper_Exception
    * 
    * @see DBHelper_Interface_LooseRecord::ERROR_UNKNOWN_DATA_KEY
    */
    public function setDataKey(string $name, string $value) : bool;
}
