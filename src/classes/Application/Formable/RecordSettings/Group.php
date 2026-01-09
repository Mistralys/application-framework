<?php
/**
 * @package Application
 * @subpackage Formable
 */

declare(strict_types=1);

use Application\Application;
use AppUtils\Interfaces\StringableInterface;

/**
 * Handles setting groups, which are used to visually group
 * elements together.
 *
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Formable_RecordSettings_Group implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
    public const ERROR_SETTING_METHOD_MISSING = 44901;
    public const ERROR_ELEMENT_METHOD_INVALID_RETURN_VALUE = 44902;
    public const ERROR_INVALID_CALLBACK = 44903;
    
    protected string $label;

   /**
    * @var array<string,Application_Formable_RecordSettings_Setting>
    */
    protected array $settings = array();
    
    protected Application_Formable_RecordSettings $manager;
    
    protected string $abstract = '';
    
   /**
    * @var array<int,array{callback:callable,arguments:array<int,mixed>}>
    */
    protected array $onInjected = array();
    
    protected bool $expanded = false;
    
    public function __construct(Application_Formable_RecordSettings $manager, string $label)
    {
        $this->manager = $manager;
        $this->label = $label;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function expand() : Application_Formable_RecordSettings_Group
    {
        $this->expanded = true;
        
        return $this;
    }
     
    /**
     * Registers a single form element that will be used
     * in the form. Use the interface of the returned
     * setting instance to further configure it.
     *
     * @param string $name
     * @return Application_Formable_RecordSettings_Setting
     */
    public function registerSetting(string $name) : Application_Formable_RecordSettings_Setting
    {
        $setting = new Application_Formable_RecordSettings_Setting($this->manager, $name);
        
        $this->settings[$name] = $setting;
        
        if($this->manager->getDefaultSettingName() === $name)
        {
            $setting->makeDefault();
        }
        
        return $setting;
    }

    /**
     * @param string|StringableInterface|NULL $abstract
     * @return $this
     */
    public function setAbstract($abstract)  : Application_Formable_RecordSettings_Group
    {
        $this->abstract = toString($abstract);
        
        return $this;
    }
    
    public function hasSettings() : bool
    {
        return !empty($this->settings);
    }

    /**
     * @return Application_Formable_RecordSettings_Setting[]
     */
    public function getSettings()  : array
    {
        return array_values($this->settings);
    }

    /**
     * @param bool $includeVirtual
     * @return string[]
     */
    public function getSettingKeyNames(bool $includeVirtual=false) : array
    {
        $result = array();

        foreach($this->settings as $setting)
        {
            if(!$includeVirtual && $setting->isVirtual())
            {
                continue;
            }

            $result[] = $setting->getName();
        }

        return $result;
    }
    
    public function inject() : void
    {
        if(empty($this->settings)) 
        {
            return;
        }
        
        $this->injectHeader();
        
        foreach($this->settings as $setting)
        {
            if($setting->isVirtual())
            {
                continue;
            }

            $this->injectSetting($setting);
        }
    }

   /**
    * Adds a callback that will be called once the group 
    * itself (not its contained form fields) has been 
    * injected into the form.
    * 
    * The callback gets the following arguments:
    * 
    * 1. The group instance ({@see Application_Formable_RecordSettings_Group})
    * 2. The form section instance ({@see UI_Page_Section})
    * 3. Any additional arguments given in the argument array
    *
    * ## Callback prototype:
    *
    * ```php
    * function(
    *     Application_Formable_RecordSettings_Group $group,
    *     UI_Page_Section $header,
    *     mixed $arg1,
    *     mixed $arg2,
    *     ...
    * )
    * ```
    * 
    * @param callable $callback
    * @param array<int,mixed> $arguments
    * @return Application_Formable_RecordSettings_Group
    */
    public function onInjected(callable $callback, array $arguments=array()) : Application_Formable_RecordSettings_Group
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_CALLBACK);
        
        $this->onInjected[] = array(
            'callback' => $callback,
            'arguments' => $arguments
        );
        
        return $this;
    }
    
    protected function injectHeader() : void
    {
        $header = $this->manager->addSection($this->label);
        
        $header->setAbstract($this->abstract);
        
        if($this->hasIcon())
        {
            $header->setIcon($this->icon);
        }
        
        if($this->expanded)
        {
            $header->expand();
        }
        
        foreach($this->onInjected as $def)
        {
            $args = $def['arguments'];
            
            array_unshift($args, $header);
            array_unshift($args, $this);
            
            call_user_func_array($def['callback'], $args);
        }
    }
    
    protected function injectSetting(Application_Formable_RecordSettings_Setting $setting) : void
    {
        $method = '';
        
        if($setting->hasCallback())
        {
            $el = $setting->executeCallback();
        }
        else
        {
            $method = 'inject_'.str_replace('-', '_', $setting->getName());
            
            if(!method_exists($this->manager, $method))
            {
                throw new Application_Exception(
                    'Setting cannot be injected, method missing.',
                    sprintf(
                        'The class [%s] must implement the method [%s].',
                        get_class($this->manager),
                        $method
                    ),
                    self::ERROR_SETTING_METHOD_MISSING
                );
            }
            
            $el = $this->manager->$method();
        }
        
        if(!$el instanceof HTML_QuickForm2_Node)
        {
            throw new Application_Exception(
                'Invalid setting method return value.',
                sprintf(
                    'The method [%s] did not return a QuickForm node instance.',
                    get_class($this->manager).':'.$method.'()'
                ),
                self::ERROR_ELEMENT_METHOD_INVALID_RETURN_VALUE
            );
        }
        
        $setting->configureElement($el);
    }
}
