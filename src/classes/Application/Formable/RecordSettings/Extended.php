<?php
/**
 * File containing the {@see Application_Formable_RecordSettings_Extended} class.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSettings_Extended
 */

declare(strict_types=1);

use Application\Disposables\DisposableDisposedException;
use AppUtils\BaseException;
use AppUtils\ConvertHelper_Exception;
use DBHelper\Interfaces\DBHelperRecordInterface;

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
    public const ERROR_FORM_NOT_SUBMITTED = 59005;

    /**
    * When in create mode, and provided the form has been
    * submitted and is valid, will create the record from
    * the form data and return it.
    * 
    * @return DBHelperRecordInterface
    * @throws BaseException
    */
    public function createRecord() : DBHelperRecordInterface
    {
        $this->initSettingsForm();

        if(!$this->isFormSubmitted()) {
            throw new Application_Formable_Exception(
                'Form has not been submitted',
                '',
                self::ERROR_FORM_NOT_SUBMITTED
            );
        }

        if(!$this->isFormValid()) {
            throw new Application_Formable_Exception(
                'Form is invalid',
                $this->renderErrorMessages(),
                self::ERROR_CREATE_FORM_INVALID
            );
        }

        $values = $this->getFormValues();

        return $this->createRecordFromValues(
            $this->collectStorageValues(new Application_Formable_RecordSettings_ValueSet($values)),
            $this->collectInternalValues(new Application_Formable_RecordSettings_ValueSet($values))
        );
    }

    /**
     * Creates a record using a set of form values independently
     * of the settings manager's form submission.
     *
     * Usage example: A wizard where the form values are stored
     * in one of the steps, and used later to create the record.
     *
     * @param Application_Formable_RecordSettings_ValueSet $recordData
     * @param Application_Formable_RecordSettings_ValueSet $internalValues
     * @return DBHelperRecordInterface
     *
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
    protected function createRecordFromValues(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : DBHelperRecordInterface
    {
        if($this->isEditMode())
        {
            throw new Application_Formable_Exception(
                'Cannot create records in edit mode',
                '',
                self::ERROR_CREATE_IN_EDIT_MODE
            );
        }

        DBHelper::requireTransaction(sprintf('Create a new %s', $this->collection->getRecordTypeName()));

        $this->getCreateData($recordData, $internalValues);

        $record = $this->collection->createNewRecord($recordData->getValues());

        $this->record = $record;
        $this->updateRecord($recordData, $internalValues);
        $this->record = null;

        $this->processPostCreateSettings($record, $recordData, $internalValues);

        $record->save();

        return $record;
    }

    /**
     * Called after the record has been created, and allows
     * additional configuration from the form values that was
     * not possible during creation, like options and the like.
     *
     * NOTE: {@see self::updateRecord()} is called before this.
     * Only things unique to do after creating a record need
     * to be done here.
     *
     * The record is saved again after this.
     *
     * @param DBHelperRecordInterface $record
     * @param Application_Formable_RecordSettings_ValueSet $recordData
     * @param Application_Formable_RecordSettings_ValueSet $internalValues
     */
    abstract protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void;

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
     * @param Application_Formable_RecordSettings_ValueSet $recordData
     * @param Application_Formable_RecordSettings_ValueSet $internalValues
     */
    abstract protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void;
    
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
        
        $storageValues = $this->collectStorageValues(
            new Application_Formable_RecordSettings_ValueSet($this->getFormValues())
        );

        $values = $storageValues->getValues();
        foreach($values as $name => $value)
        {
            $this->record->setRecordKey($name, $value);
        }

        $this->updateRecord($storageValues, $this->collectInternalValues(new Application_Formable_RecordSettings_ValueSet($this->getFormValues())));
        
        return $this->record->save();
    }

    private function collectInternalValues(Application_Formable_RecordSettings_ValueSet $data) : Application_Formable_RecordSettings_ValueSet
    {
        $settings = $this->getSettings();
        $result = new Application_Formable_RecordSettings_ValueSet(array());

        foreach ($settings as $setting)
        {
            if(!$setting->isStatic() && !$setting->isInternal() && !$setting->isVirtual()) {
                continue;
            }

            $name = $setting->getName();

            if($data->keyExists($name))
            {
                $value = $data->getKey($name);
            }
            else
            {
                $value = $setting->getDefaultValue();
            }

            $result->setKey($name, $value);
        }

        return $result;
    }

    /**
     * Called when the record should be saved when in edit mode.
     * Simply use the provided form data to update additional
     * record properties that cannot be modified automatically.
     *
     * @param Application_Formable_RecordSettings_ValueSet $recordData
     * @param Application_Formable_RecordSettings_ValueSet $internalValues
     */
    abstract protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues) : void;

    /**
     * Imports raw form values into the settings form.
     *
     * Used in wizards with a setting screen, for example.
     * The valid form data is stored, and later used to
     * create the record.
     *
     * @param array<string,mixed> $formValues
     * @return $this
     * @throws Application_Exception
     */
    public function makeSubmitted(array $formValues = array()): self
    {
        $this->inject();

        return parent::makeSubmitted($formValues);
    }
}
