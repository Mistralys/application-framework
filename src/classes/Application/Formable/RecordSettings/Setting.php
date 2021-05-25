<?php
/**
 * File containing the {@see Application_Formable_RecordSettings_Setting} class.
 * 
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSettings_Setting
 */

declare(strict_types=1);

/**
 * Handles individual record settings and their configuration.
 * Used to track for example if the element is required and that
 * sort of information.
 * 
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Formable_RecordSettings_Setting
{
    const ERROR_INVALID_CALLBACK = 45301;
    const ERROR_NO_CALLBACK_EXECUTABLE = 45302;
    const ERROR_STORAGE_FILTER_CALLBACK_FAILED = 45303;
    
   /**
    * @var Application_Formable_RecordSettings
    */
    protected $settings;
    
   /**
    * @var string
    */
    protected $name;
    
   /**
    * @var bool
    */
    protected $default = false;
    
   /**
    * @var bool
    */
    protected $required = false;
    
   /**
    * @var array|NULL
    */
    protected $callback = null;

    /**
     * @var string|array|bool|int|float
     */
    protected $defaultValue = '';

    /**
     * @var bool
     */
    protected $internal = false;

    /**
     * @var callable|NULL
     */
    protected $storageFilter;

    /**
     * @var bool
     */
    protected $virtual = false;

    /**
     * @var string
     */
    protected $storageName = '';

    /**
     * @var bool
     */
    private $static = false;

    public function __construct(Application_Formable_RecordSettings $settings, string $name)
    {
        $this->settings = $settings;
        $this->name = $name;
    }

    public function setStorageName(string $name) : Application_Formable_RecordSettings_Setting
    {
        $this->storageName = $name;
        return $this;
    }

    public function getStorageName() : string
    {
        if(!empty($this->storageName)) {
            return $this->storageName;
        }

        return $this->name;
    }

    public function getName() : string
    {
        return $this->name;
    }
    
    public function isDefault() : bool
    {
        return $this->default;
    }

    /**
     * @return array|bool|float|int|string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string|array|bool|int|float $value
     * @return $this
     */
    public function setDefaultValue($value) : Application_Formable_RecordSettings_Setting
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Marks the setting as internal, which means it will only
     * be used within the settings form. Its value will not be
     * used when saving the record data.
     *
     * Use case 1:
     *
     * Fields that are interpreted to adjust the record's data set.
     * See the method `setStorageFilter()` for details on how to
     * handle this.
     *
     * Use case 2:
     *
     * Collect data that is used only once the record has been
     * created or updated, like data stored in separate tables.
     * See the method `_afterSave()` for details.
     *
     * @return $this
     *
     * @see Application_Formable_RecordSettings_Setting::setStorageFilter()
     * @see Application_Formable_RecordSettings::_afterSave()
     */
    public function makeInternal() : Application_Formable_RecordSettings_Setting
    {
        $this->internal = true;
        return $this;
    }

    public function isInternal() : bool
    {
        return $this->internal;
    }

    /**
     * Marks the setting as static, which means it does not need
     * to have a value in the form. It can be used for visual only
     * elements, like static content to display information.
     *
     * NOTE: It does not exclude the setting from having a value.
     *
     * @return $this
     */
    public function makeStatic() : Application_Formable_RecordSettings_Setting
    {
        $this->static = true;

        return $this;
    }

    public function isStatic() : bool
    {
        return $this->static;
    }

    /**
     * Turns the setting into a virtual element that does
     * not have any matching form element. Use this for
     * setting fixed data keys to pass through.
     *
     * @param mixed $value
     * @return $this
     */
    public function makeVirtual($value) : Application_Formable_RecordSettings_Setting
    {
        $this->virtual = true;
        $this->setDefaultValue($value);
        return $this;
    }

    public function isVirtual() : bool
    {
        return $this->virtual;
    }

    /**
     * Sets a filter callback that will be used when the record
     * is saved: it can be used to adjust the data set before it
     * is saved according to the setting value.
     *
     * Example: Selecting a save option from a list, which modifies
     * the data set accordingly.
     *
     * The callback method gets the following parameters:
     *
     * - The value of the setting
     * - The data set instance (Application_Formable_RecordSettings_ValueSet)
     * - This setting instance
     *
     * @param callable $filter
     * @return $this
     * @throws Application_Exception
     *
     * @see Application_Formable_RecordSettings_ValueSet
     */
    public function setStorageFilter($filter) : Application_Formable_RecordSettings_Setting
    {
        Application::requireCallableValid($filter);

        $this->storageFilter = $filter;

        return $this;
    }

   /**
    * Marks this setting as the default form element.
    * 
    * NOTE: This is done automatically, no need to 
    * call it manually.
    * 
    * @return Application_Formable_RecordSettings_Setting
    */
    public function makeDefault() : Application_Formable_RecordSettings_Setting
    {
        $this->default = true;
        return $this;
    }
    
   /**
    * Marks the form element as required: adds
    * the required rule automatically to the element.
    * 
    * @return Application_Formable_RecordSettings_Setting
    */
    public function makeRequired() : Application_Formable_RecordSettings_Setting
    {
        $this->required = true;
        return $this;
    }
    
    public function isRequired() : bool
    {
        return $this->required;
    }
    
   /**
    * Configures the form element that was created for the
    * setting with the specified configuration.
    * 
    * @param HTML_QuickForm2_Element $el
    */
    public function configureElement(HTML_QuickForm2_Element $el) : void
    {
        if($this->isDefault())
        {
            $this->settings->setDefaultElement($el);
        }
        
        if($this->isRequired())
        {
            $this->settings->makeRequired($el);
        }
    }
    
   /**
    * Sets a custom callback to use to inject the element.
    * 
    * @param callable $callback
    * @param array $arguments An array of arguments to pass on to the callback. The first parameter is always the setting instance.
    * @return Application_Formable_RecordSettings_Setting
    */
    public function setCallback($callback, array $arguments=array()) : Application_Formable_RecordSettings_Setting
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_CALLBACK);
        
        $this->callback = array(
            'callback' => $callback,
            'arguments' => $arguments
        );
        
        return $this;
    }
    
    public function hasCallback() : bool
    {
        return isset($this->callback);
    }
    
    public function executeCallback() : HTML_QuickForm2_Element
    {
        if($this->callback === null) 
        {
            throw new Application_Exception(
                'No callback set',
                sprintf(
                    'The setting [%s] has no callback set, so no callback can be executed.',
                    $this->getName()
                ),
                self::ERROR_NO_CALLBACK_EXECUTABLE
            );
        }
        
        $args = $this->callback['arguments'];
        
        array_unshift($args, $this);
        
        $result = call_user_func_array($this->callback['callback'], $args);
        
        if($result instanceof HTML_QuickForm2_Element)
        {
            return $result;
        }
        
        throw new Application_Exception(
            'Invalid callback return value',
            sprintf(
                'The callback of setting [%s] did not return a [HTML_QuickForm2_Element] instance. Returned: [%s].',
                $this->getName(),
                \AppUtils\parseVariable($result)->enableType()->toString()
            ),
            self::ERROR_NO_CALLBACK_EXECUTABLE
        );
    }

    /**
     * Filters the specified value for storage, to be used in the record's
     * data set to be saved.
     *
     * @param mixed $value
     * @param Application_Formable_RecordSettings_ValueSet $values
     * @return mixed
     */
    public function filterForStorage($value, Application_Formable_RecordSettings_ValueSet $values)
    {
        if(!isset($this->storageFilter)) {
            return $value;
        }

        $result = call_user_func($this->storageFilter, $value, $values, $this);

        if($result !== false) {
            return $result;
        }

        throw new Application_Exception(
            'Failed to call the storage filter.',
            sprintf(
                'The user function storage callback [%s] returned a boolean false.',
                \AppUtils\parseVariable($this->storageFilter)->enableType()->toString()
            ),
            self::ERROR_STORAGE_FILTER_CALLBACK_FAILED
        );
    }
}
