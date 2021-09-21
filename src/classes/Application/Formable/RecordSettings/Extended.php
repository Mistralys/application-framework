<?php
/**
 * File containing the {@see Application_Formable_RecordSettings_Extended} class.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSettings_Extended
 */

declare(strict_types=1);

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
    const ERROR_CREATE_FORM_INVALID = 59001;
    const ERROR_CREATE_IN_EDIT_MODE = 59002;
    const ERROR_CANNOT_SAVE_IN_CREATE_MODE = 59003;
    const ERROR_EDIT_FORM_INVALID = 59004;
    
   /**
    * When in create mode, and provided the form has been
    * submitted and is valid, will create the record from
    * the form data and return it.
    * 
    * @return DBHelper_BaseRecord
    * @throws Application_Exception|DBHelper_Exception
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
        
        return $this->createRecordFromValues($this->getFormValues());
    }

    /**
     * Creates a record using a set of form values independently
     * of the settings manager's form submission.
     *
     * Usage example: A wizard where the form values are stored
     * in one of the steps, and used later to create the record.
     *
     * @param array<string,mixed> $values
     * @return DBHelper_BaseRecord
     *
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws DBHelper_Exception
     */
    public function createRecordFromValues(array $values) : DBHelper_BaseRecord
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

        $data = $this->getCreateData($values);

        $record = $this->collection->createNewRecord($data);

        $this->processPostCreateSettings($record, $values);

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
     * @param array<string,mixed> $formValues
     */
    abstract protected function processPostCreateSettings(DBHelper_BaseRecord $record, array $formValues) : void;

   /**
    * Retrieves the data array to use to create the new record
    * using the collection's createNewRecord method. Use the 
    * form values to compile the necessary columns.
    * 
    * @param array $formValues
    * @return array
    */
    abstract protected function getCreateData(array $formValues) : array;
    
   /**
    * When in edit mode, and provided the form has been 
    * submitted and is valid, updates the record using
    * the form values and saves it.
    * 
    * @return bool
    * @throws Application_Exception|DBHelper_Exception
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
        
        $values = $this->getFormValues();
        
        $this->updateRecord($values);
        
        return $this->record->save();
    }
    
   /**
    * Called when the record should be saved when in edit mode.
    * Simply use the provided form data to update the record 
    * properties as applicable.
    * 
    * @param array $values
    */
    abstract protected function updateRecord(array $values) : void;
}
