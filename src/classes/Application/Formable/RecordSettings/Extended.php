<?php
/**
 * File containing the {@see Application_Formable_RecordSettings_Extended} class.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSettings_Extended
 */

declare(strict_types=1);

use AppUtils\BaseException;
use AppUtils\ConvertHelper_Exception;

/**
 * Adds create and save capabilities to the base record settings form.
 * 
 * Usage: 
 * 
 * - Extend this class
 * - Implement the two abstract methods
 * - Call createRecord() to have a record created from the form
 * - Call saveRecord() to save the selected record from the form
 *
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Formable_RecordSettings_Extended extends Application_Formable_RecordSettings
{
    public const ERROR_CREATE_FORM_INVALID = 59001;
    public const ERROR_CREATE_IN_EDIT_MODE = 59002;
    public const ERROR_CANNOT_SAVE_IN_CREATE_MODE = 59003;
    public const ERROR_EDIT_FORM_INVALID = 59004;
    
   /**
    * When in create mode, and provided the form has been
    * submitted and is valid, will create the record from
    * the form data and return it.
    * 
    * @return DBHelper_BaseRecord
    * @throws BaseException
    */
    public function createRecord() : DBHelper_BaseRecord
    {
        $this->initSettingsForm();
        
        if(!$this->isFormValid())
        {
            throw new Application_Exception(
                'Form is invalid',
                '',
                self::ERROR_CREATE_FORM_INVALID
            );
        }
        
        return $this->createRecordFromValues(
            $this->filterForStorage(new Application_Formable_RecordSettings_ValueSet($this->getFormValues()))
        );
    }

    /**
     * Creates a record using a set of form values independently
     * of the settings manager's form submission.
     *
     * Usage example: A wizard where the form values are stored
     * in one of the steps, and used later to create the record.
     *
     * @param Application_Formable_RecordSettings_ValueSet $valueSet
     * @return DBHelper_BaseRecord
     *
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    protected function createRecordFromValues(Application_Formable_RecordSettings_ValueSet $valueSet) : DBHelper_BaseRecord
    {
        if($this->isEditMode())
        {
            throw new Application_Exception(
                'Cannot create records in edit mode',
                '',
                self::ERROR_CREATE_IN_EDIT_MODE
            );
        }

        DBHelper::requireTransaction(sprintf('Create a new %s', $this->collection->getRecordTypeName()));

        $this->getCreateData($valueSet);

        $record = $this->collection->createNewRecord($valueSet->getValues());

        $this->processPostCreateSettings($record, $valueSet);

        $record->save();

        return $record;
    }

    /**
     * Called after the record has been created, and allows
     * additional configuration from the form values that was
     * not possible during creation, like options and the like.
     *
     * The record is saved again after this.
     *
     * @param DBHelper_BaseRecord $record
     * @param Application_Formable_RecordSettings_ValueSet $valueSet
     */
    abstract protected function processPostCreateSettings(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $valueSet) : void;

   /**
    * Retrieves the data array to use to create the new record
    * using the collection's createNewRecord method.
    *
    * NOTE: If the settings have been given a storage name using
    * {@see Application_Formable_RecordSettings_Setting::setStorageName()},
    * the value will be stored in the data array under that name.
    *
    * TIP: When working with storage names, only the values that
    * have no default value must be added to the resulting array.
    *
    * @param Application_Formable_RecordSettings_ValueSet $valueSet
    */
    abstract protected function getCreateData(Application_Formable_RecordSettings_ValueSet $valueSet) : void;
    
   /**
    * When in edit mode, and provided the form has been 
    * submitted and is valid, updates the record using
    * the form values and saves it.
    * 
    * @return bool
    * @throws BaseException
    */
    public function saveRecord() : bool
    {
        $this->initSettingsForm();
        
        if(!$this->isFormValid())
        {
            throw new Application_Exception(
                'Form is invalid',
                '',
                self::ERROR_EDIT_FORM_INVALID
            );
        }
        
        if(!$this->isEditMode())
        {
            throw new Application_Exception(
                'Cannot save in create mode',
                '',
                self::ERROR_CANNOT_SAVE_IN_CREATE_MODE
            );
        }
        
        DBHelper::requireTransaction('Save a record from a form');
        
        $valueSet = $this->filterForStorage(
            new Application_Formable_RecordSettings_ValueSet($this->getFormValues())
        );

        $values = $valueSet->getValues();
        foreach($values as $name => $value)
        {
            $this->record->setRecordKey($name, $value);
        }

        $this->updateRecord($valueSet);
        
        return $this->record->save();
    }
    
   /**
    * Called when the record should be saved when in edit mode.
    * Simply use the provided form data to update additional
    * record properties that cannot be modified automatically.
    * 
    * @param Application_Formable_RecordSettings_ValueSet $valueSet
    */
    abstract protected function updateRecord(Application_Formable_RecordSettings_ValueSet $valueSet) : void;
}
