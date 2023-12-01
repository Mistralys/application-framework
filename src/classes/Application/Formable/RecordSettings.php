<?php
/**
 * File containing the {@see Application_Formable_RecordSettings_Setting} class.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSettings_Setting
 */

declare(strict_types=1);

use AppUtils\BaseException;

/**
 * Used to make handling record setting forms easier:
 * Allows registering the settings that are used in
 * the form, and how they should be handled.
 * 
 * Each form element gets its own method to keep things
 * readable, and things like the required status of
 * elements is handled automatically.
 * 
 * Usage:
 * 
 * - Extend this class
 * - Register settings in the <code>registerSettings</code> method
 * - Add the <code>inject_(setting name)</code> methods for all settings
 * - Instantiate the class with the target formable instance
 * - Call the <code>inject()</code> method
 *
 * NOTE: To add create & save capabilities, use the sister class
 * {@see Application_Formable_RecordSettings_Extended} instead.
 *
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see Application_Formable_RecordSettings_Extended
 */
abstract class Application_Formable_RecordSettings extends Application_Formable_Container implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_NOTHING_TO_INJECT = 44801;
    public const ERROR_SETTING_NAME_DOES_NOT_EXIST = 44802;
    
   /**
    * @var DBHelper_BaseRecord|NULL
    */
    protected $record;
    
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;
    
   /**
    * @var Application_Formable_RecordSettings_Group[]
    */
    protected $groups = array();
    
   /**
    * @var bool
    */
    protected $settingsInitialized = false;
    
   /**
    * @var boolean
    */
    protected $settingsFormInitialized = false;

    /**
     * Whether using {@see Application_Formable_RecordSettings::getDefaultValues()}
     * will use the setting's storage names to fetch the data.
     *
     * @var bool
     * @see Application_Formable_RecordSettings::setDefaultsUseStorageNames()
     */
    private bool $defaultsUseStorage = false;

    public function __construct(Application_Formable $formable, DBHelper_BaseCollection $collection, ?DBHelper_BaseRecord $record=null)
    {
        parent::__construct($formable);
        
        $this->record = $record;
        $this->collection = $collection;
    }
    
    final protected function handleSettings() : void
    {
        if(!$this->settingsInitialized)
        {
            $this->log('Initializing the settings.');

            $this->initSettings();
            $this->registerSettings();
            $this->settingsInitialized = true;
        }
    }

    /**
     * Retrieves a list of the names of all registered settings
     * (=the names of the form elements).
     *
     * @param bool $includeVirtual Whether to include virtual settings in the list.
     * @return string[]
     */
    public function getSettingKeyNames(bool $includeVirtual=false) : array
    {
        $result = array();

        foreach ($this->groups as $group)
        {
            $result = array_merge($result, $group->getSettingKeyNames($includeVirtual));
        }

        return $result;
    }
    
    public final function isEditMode() : bool
    {
        return isset($this->record);
    }

    // region: Overridable methods

    /**
     * Called when a record has been newly created or an existing one updated
     * using the settings form.
     *
     * @param DBHelper_BaseRecord $record
     * @param Application_Formable_RecordSettings_ValueSet $data
     */
    protected function _afterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data) : void
    {

    }

    /**
     * Called immediately before the form's elements are injected.
     */
    protected function preInject() : void
    {

    }

    /**
     * Called immediately before the settings are registered.
     */
    protected function initSettings() : void
    {

    }

    // endregion

    // region: Abstract methods

    /**
    * Called to register all settings (form elements) that are
    * present in the form.
     *
     * Usage:
     *
     * Use the {@see self::addGroup()} method to add groups
     * of settings, in which the individual settings can
     * be added.
    */
    abstract protected function registerSettings() : void;
    
   /**
    * The name of the default form element in the form:
    * will automatically be focused on.
    * 
    * @return string
    */
    abstract public function getDefaultSettingName() : string;

    abstract public function isUserAllowedEditing() : bool;

    // endregion

   /**
    * Injects all form elements into the  
    * target formable instance.
    * 
    * @throws Application_Exception
    */
    final public function inject() : Application_Formable_RecordSettings
    {
        $this->initSettingsForm();
        
        $this->preInject();
        
        $injected = false;
        
        foreach($this->groups as $group)
        {
            if($group->hasSettings()) 
            {
                $group->inject();
                $injected = true;
            }
        }

        if(!$this->isUserAllowedEditing())
        {
            $this->makeReadonly();
        }

        if(!$injected)
        {
            throw new Application_Exception(
                'No settings to inject',
                sprintf(
                    'No groups had any settings to inject in the class [%s].',
                    get_class($this)
                ),
                self::ERROR_NOTHING_TO_INJECT
            );
        }
        
        return $this;
    }
    
    final protected function initSettingsForm() : void
    {
        if($this->settingsFormInitialized)
        {
            return;
        }
        
        $this->handleSettings();
        
        if(!$this->originFormable->isInitialized())
        {
            $this->originFormable->createFormableForm($this->getFormName(), $this->getDefaultValues());
        }
        
        $this->addFormablePageVars();
        
        if($this->isEditMode())
        {
            $this->addHiddenVar(
                $this->collection->getRecordPrimaryName(), 
                (string)$this->record->getID()
            );
        }
        
        $foreign = $this->collection->getForeignKeys();
        
        foreach($foreign as $name => $val)
        {
            $this->addHiddenVar($name, $val);
        }
    
        $this->settingsFormInitialized = true;
    }

    /**
     * Whether using {@see Application_Formable_RecordSettings::getDefaultValues()}
     * will use the specified storage data key names if specified via
     * {@see Application_Formable_RecordSettings_Setting::setStorageName()}.
     * The default is to use the setting name.
     *
     * HINT: Use this if the settings manager does not use the same names
     * as the record's data keys, or to make sure that they can diverge in
     * the future without breaking the default values.
     *
     * This method was added to stay compatible with older code.
     *
     * @param bool $useStorageNames
     * @return $this
     */
    public function setDefaultsUseStorageNames(bool $useStorageNames) : self
    {
        $this->defaultsUseStorage = $useStorageNames;
        return $this;
    }

    public function getDefaultValues() : array
    {
        $this->handleSettings();

        $values = array();

        if($this->isEditMode())
        {
            $values = $this->importRecordValues($this->record->getFormValues());
        }

        $settings = $this->getSettings();

        // We add the default values of settings regardless
        // of whether we are in edit mode: there may be settings
        // that are not directly mapped to record data keys.
        foreach($settings as $setting)
        {
            $name = $setting->getName();

            if(!isset($values[$name]))
            {
                $values[$name] = $setting->getDefaultValue();
            }
        }

        return $values;
    }

    /**
     * Converts the key names in the form values retrieved
     * by the record when the defaults are set to use the
     * storage names.
     *
     * Defaults disabled (default) = Key names are the record's column names
     * Defaults enabled            = Key names are the settings manager's setting names
     *
     * @param array<string,mixed> $values
     * @return array<string,mixed>
     */
    private function importRecordValues(array $values) : array
    {
        $result = array();
        $settings = $this->getSettings();

        $valueSet = new Application_Formable_RecordSettings_ValueSet($values);

        foreach ($settings as $setting)
        {
            $name = $setting->getName();
            if($this->defaultsUseStorage) {
                $name = $setting->getStorageName();
            }

            $result[$setting->getName()] = $setting->filterForImport($valueSet->getKey($name), $valueSet);
        }

        return $result;
    }

    /**
     * Retrieves all registered settings, in a flat list
     * without groups. They retain the order in which they
     * come when grouped.
     *
     * @return Application_Formable_RecordSettings_Setting[]
     */
    public final function getSettings() : array
    {
        $this->handleSettings();
        
        $result = array();

        foreach ($this->groups as $group)
        {
            $result = array_merge($result, $group->getSettings());
        }

        return $result;
    }
    
    public function getFormName() : string
    {
        $name = $this->collection->getDataGridName();
        
        if($this->isEditMode())
        {
            return $name.'-edit';
        }
        
        return $name.'-create';
    }
    
    final protected function addGroup(string $label) : Application_Formable_RecordSettings_Group
    {
        $group = new Application_Formable_RecordSettings_Group($this, $label);
            
        $this->groups[] = $group;
            
        return $group;
    }

    /**
     * Retrieves a setting by its name or storage name (if any).
     *
     * @param string $name
     * @return Application_Formable_RecordSettings_Setting
     * @throws Application_Exception
     */
    public function getSettingByName(string $name) : Application_Formable_RecordSettings_Setting
    {
        $settings = $this->getSettings();

        foreach($settings as $setting) {
            if($setting->getName() === $name || $setting->getStorageName() === $name) {
                return $setting;
            }
        }

        throw new Application_Exception(
            'Unknown form setting',
            sprintf(
                'Tried to fetch the setting [%s]. Available settings are [%s].',
                $name,
                implode(', ', $this->getSettingKeyNames(true))
            ),
            self::ERROR_SETTING_NAME_DOES_NOT_EXIST
        );
    }

    /**
     * @param Application_Formable_RecordSettings_ValueSet $data
     * @return Application_Formable_RecordSettings_ValueSet A copy of the specified data, with the filtered values.
     * @throws BaseException
     */
    final public function collectStorageValues(Application_Formable_RecordSettings_ValueSet $data) : Application_Formable_RecordSettings_ValueSet
    {
        $settings = $this->getSettings();
        $result = new Application_Formable_RecordSettings_ValueSet(array());

        foreach ($settings as $setting)
        {
            if($setting->isStatic() || $setting->isInternal()) {
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

            $result->setKey($setting->getStorageName(), $setting->filterForStorage($value, $data));
        }

        return $result;
    }

    final public function afterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data) : void
    {
        $this->logEvent('AfterSave', sprintf('The record [%s] has been saved.', $record->getID()));

        $this->_afterSave($record, $data);
    }

    /**
     * Retrieves an associative array of all virtual values
     * that have been defined in the settings.
     *
     * @return array<string,mixed>
     */
    final public function getVirtualValues() : array
    {
        $result = array();

        $settings = $this->getSettings();

        foreach ($settings as $setting)
        {
            if($setting->isVirtual() || $setting->isStatic())
            {
                $result[$setting->getName()] = $setting->getDefaultValue();
            }
        }

        return $result;
    }

    public function getLogIdentifier(): string
    {
        if(isset($this->record))
        {
            return sprintf(
                'RecordSettings [%s] | Edit Mode [# %s]',
                $this->collection->getRecordTypeName(),
                $this->record->getID()
            );
        }

        return sprintf(
            'RecordSettings [%s] | Create Mode',
            $this->collection->getRecordTypeName()
        );
    }
}
