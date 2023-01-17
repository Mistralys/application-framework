<?php
/**
 * File containing the {@link Application_FilterSettings} class.
 *
 * @package Application
 * @subpackage Filtering
 * @see Application_FilterSettings
 */

use Application\Driver\DriverException;
use Application\Exception\UnexpectedInstanceException;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Interface_Stringable;

/**
 * Base class for custom filter setting implementations. This can
 * be used to create a settings form intended to configure a filter
 * criteria instance. The storage of the settings is handled
 * automatically on a per-user basis.
 *
 * Usage:
 *
 * - Extend this class, and implement the abstract methods
 * - Instantiate an instance of the class
 * - Add it to a sidebar using the {@link UI_Page_Sidebar::addFilterSettings()} method
 * - Configure your filter criteria using the {@link Application_FilterSettings::configureFilters()} method
 *
 * @package Application
 * @subpackage Filtering
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterSettings implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_UNKNOWN_SETTING = 450001;
    public const ERROR_NO_SETTINGS_REGISTERED = 450002;
    public const ERROR_MISSING_AUTOCONFIG_METHOD = 450003;
    public const ERROR_ONLY_ONE_MORE_ALLOWED = 450004;
    public const ERROR_MISSING_ID = 450005;
    public const SETTING_PREFIX = 'filter_settings_';

    protected string $id;
    protected string $jsID;
    protected Application_Request $request;
    protected Application_User $user;

    /**
     * @var array<string,string|number|array<mixed>|bool|NULL>
     */
    private array $settings = array();

    /**
     * @var array<string,array{label:string,default:string|number|array<mixed>|bool|NULL,elementID:string}>
     */
    protected array $definitions = array();

    /**
     * @var array<string,mixed>
     */
    protected $defaultSettings;

    /**
     * @var string[]
     */
    protected array $autoConfigure = array();
    
   /**
    * Available during the injectElements method.
    * @var HTML_QuickForm2_Container
    */
    protected $container;
    
    protected UI $ui;
    
   /**
    * @var array<string,string|number|bool>
    */
    protected array $hiddens = array();

    protected array $enabledStatus = array();
    protected string $storageID;
    private bool $settingsLoaded = false;

    /**
     * @param string $id The ID is used to persist the settings.
     *
     * @throws Application_Exception
     * @throws UI_Exception
     * @throws DriverException
     */
    public function __construct(string $id)
    {
        $this->setID($id);

        $this->jsID = nextJSID();
        $this->user = Application::getUser();
        $this->request = Application_Driver::getInstance()->getRequest();
        $this->ui = UI::getInstance();

        $this->log('RegisterSettings | Letting the class register its settings.');

        $this->registerSettings();
        
        if(empty($this->definitions)) {
            throw new Application_Exception(
                'No settings registered',
                'The [registerSettings] method has been implemented, but it does not register any settings.',
                self::ERROR_NO_SETTINGS_REGISTERED
            );
        }
        
        $this->init();
    }

    /**
     * Sets the ID to use for saving settings (on a per-user basis).
     * Using the same ID for different instances means they will
     * share stored settings.
     *
     * NOTE: This should be called before the form is rendered, ideally
     * before doing anything so the settings do not get loaded several
     * times. Settings use lazy loading, but this way the chance is
     * minimised.
     *
     * @param string $id
     * @return $this
     */
    public function setID(string $id) : self
    {
        if(empty($id))
        {
            throw new Application_Exception(
                'An empty ID for filter settings is not allowed.',
                '',
                self::ERROR_MISSING_ID
            );
        }

        if(isset($this->id))
        {
            $this->log('Changing ID to [%s].', $id);
        }

        $this->id = $id;
        $this->storageID = self::SETTING_PREFIX.$id;
        $this->settingsLoaded = false;
        $this->cachedLogIdentifier = null;



        return $this;
    }
    
    protected function init()
    {
        
    }

    /**
     * @param array<string,string|number|Interface_Stringable|NULL> $vars
     * @return $this
     */
    public function addHiddenVars(array $vars) : self
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }
        
        return $this;
    }
    
    /**
     * Adds a hidden var to the filter form.
     * @param string $name
     * @param string|number|Interface_Stringable|NULL $value
     * @return $this
     */
    public function addHiddenVar(string $name, $value) : self
    {
        $this->hiddens[$name] = toString($value);
        return $this;
    }
    
    /**
     * Registers all settings handled by the class using
     * the {@link registerSetting()} method. At least one
     * setting must be registered.
     */
    abstract protected function registerSettings() : void;
    
   /**
    * Injects the required filtering form elements into the provided
    * form container.
    * 
    * NOTE: Legacy method. The new way is to add methods called
    * <code>inject_(setting_name)</code> for each setting.
    *
    * @deprecated
    * @param HTML_QuickForm2_Container $container
    * 
    * @see Application_FilterSettings::injectElementsContainer()
    */
    protected function injectElements(HTML_QuickForm2_Container $container) : void
    {
        
    }
    
    /**
     * Configures the provided filter criteria instance with the
     * current filtering settings.
     */
    abstract protected function _configureFilters() : void;
    
   /**
    * Automatically configures search terms in the filters
    * if a search setting is present. Meant to be used during
    * the <code>_configureFilters()</code> method.
    * 
    * @param string $setting
    */
    protected function configureSearch(string $setting='search') : void
    {
        $search = $this->getSetting($setting);
        
        if(strlen($search) >= 2) 
        {
            $this->filters->setSearch($search);
        }
    }
    
    protected function injectElementsContainer(HTML_QuickForm2_Container $container)
    {
        $this->loadSettings();

        $this->container = $container;
        
        $this->injectElements($container);
        
        $names = array_keys($this->settings);
        
        foreach($names as $name)
        {
            if(!$this->isSettingEnabled($name))
            {
                continue;
            }

            $method = 'inject_'.str_replace('-', '_', $name);
            if(method_exists($this, $method)) {
                $this->$method();
            }
        }
    }
    
   /**
    * @var Application_FilterCriteria
    */
    protected $filters;
    
    /**
     * Configures the provided filter criteria instance with the
     * current filtering settings.
     * 
     * @param Application_FilterCriteria $filters
     */
    public function configureFilters(Application_FilterCriteria $filters) : void
    {
        $this->filters = $filters;
        
        foreach($this->autoConfigure as $method) {
            $this->$method();
        }
        
        $this->_configureFilters();
    }
    
   /**
    * Adds a setting to be configured automatically when the 
    * {@link configureFilters()} method is called. This requires
    * a matching method called <code>autoConfigure_settingName</code>
    * to be implemented, which configures the setting.
    * 
    * @param string $settingName
    * @throws Application_Exception
    */
    protected function addAutoConfigure(string $settingName) : void
    {
        $method = 'autoConfigure_'.$settingName;
        if(method_exists($this, $method)) {
            if(!in_array($method, $this->autoConfigure, true)) {
                $this->autoConfigure[] = $method;
            }
            return;
        }
        
        throw new Application_Exception(
            'Missing auto configure method',
            sprintf(
                'The filter settings class [%s] must implement the [%s] method to automatically configure the [%s] property.',
                get_class($this),
                $method,
                $settingName
            ),
            self::ERROR_MISSING_AUTOCONFIG_METHOD
        );
    }
    
    /**
     * Registers a setting that is handled by the settings.
     *
     * @param string $name
     * @param string|number|Interface_Stringable|NULL $label
     * @param mixed $default
     */
    protected function registerSetting(string $name, $label, $default=null) : void
    {
        $this->log('RegisterSettings | Registered setting [%s].', $name);

        $this->defaultSettings[$name] = $default;
        
        $this->definitions[$name] = array(
            'label' => toString($label),
            'default' => $default,
            'elementID' => $this->jsID.'-'.$name
        );
    }
    
    /**
     * Loads the currently stored settings.
     */
    protected function loadSettings() : void
    {
        if($this->settingsLoaded) {
            return;
        }

        $this->log('LoadSettings | Loading the user\'s saved settings.');

        $this->settingsLoaded = true;

        $settings = $this->defaultSettings;
        $json = $this->user->getSetting($this->storageID);
        $data = array();

        if (!empty($json))
        {
            $data = JSONConverter::json2arraySilent($json);
            $this->log('LoadSettings | Found [%s] settings.', count($data));
        }
        else
        {
            $this->log('LoadSettings | None found: Empty, or could not be decoded.');
        }

        $names = array_keys($settings);
        foreach ($names as $name)
        {
            if (isset($data[$name]))
            {
                $settings[$name] = $data[$name];
            }
        }

        $this->settings = $settings;

        $this->log(
            'LoadSettings | Calling post-load handler method [%s].',
            array($this, 'handle_settingsLoaded')[1]
        );

        $this->handle_settingsLoaded();
    }
    
    protected function handle_settingsLoaded()
    {
        
    }

    /**
     * Saves the settings when the user has submitted the
     * filters form.
     */
    protected function saveSettings() : void
    {
        $this->loadSettings();

        $this->log('Saving | Storing values.');

        $values = $this->form->getValues();
        
        if($this->request->getBool('reset')) {
            $values = $this->defaultSettings;
        }
        
        // remove all unneeded request parameters to avoid them
        // being present in the refresh URL.
        $toRemove = array_merge(array('reset', 'apply', $this->form->getTrackingName()), array_keys($this->definitions));
        $this->request->removeParams($toRemove);
        
        $url = $this->request->buildRefreshURL();
        
        $this->settings = $values;
        
        $this->user->setArraySetting($this->storageID, $values);
        $this->user->saveSettings();

        Application_Driver::getInstance()->redirectTo($url);
    }

    // region: Access setting values

    /**
     * Retrieves all settings as an associative array with
     * setting name > value pairs.
     *
     * @return array<string,string|number|array<mixed>|bool|NULL>
     */
    public function getSettings() : array
    {
        $this->loadSettings();

        $settings = array();
        $names = array_keys($this->settings);
        foreach($names as $name) {
            $settings[$name] = $this->getSetting($name);
        }
        
        return $settings;
    }
    
    /**
     * Retrieves a single setting's value. If no value
     * has been explicitly set, returns the default value.
     *
     * @param string $name
     * @return string|array<mixed>|number|bool|NULL
     */
    public function getSetting(string $name)
    {
        $this->requireSetting($name);
        $this->loadSettings();
        
        if(isset($this->settings[$name])) {
            return $this->settings[$name];
        }
        
        return $this->definitions[$name]['default'];
    }

    /**
     * @param string $name
     * @return array<mixed>
     */
    public function getArraySetting(string $name) : array
    {
        $value = $this->getSetting($name);
        if(!empty($value) && is_array($value)) {
            return $value;
        }
        
        return array();
    }

    public function getSettingString(string $name) : string
    {
        return (string)$this->getSetting($name);
    }

    public function getSettingBool(string $name) : bool
    {
        return ConvertHelper::string2bool($this->getSetting($name));
    }

    public function getSettingInt(string $name) : int
    {
        return (int)$this->getSetting($name);
    }

    // endregion

    /**
     * @param string $name
     * @param string|number|array<mixed>|bool|NULL $value
     * @return $this
     * @throws JsonException
     */
    public function setSetting(string $name, $value) : self
    {
        $this->loadSettings();

        $this->settings[$name] = $value;
        return $this;
    }

    public function setSettingEnabled(string $name, bool $enabled) : self
    {
        $this->enabledStatus[$name] = $enabled;
        return $this;
    }

    public function isSettingEnabled(string $name) : bool
    {
        if(!isset($this->enabledStatus[$name])) {
            return true;
        }

        return $this->enabledStatus[$name] !== false;
    }
    
    /**
     * @var UI_Form
     */
    protected $form;
    
    /**
     * Creates the filter form instance and configures it
     * for use with the filters. This includes buttons for
     * applying and resetting the filters.
     */
    protected function createForm() : void
    {
        $this->form = $this->ui->createForm($this->storageID, $this->getSettings());
        $this->form->makeCondensed();
        $this->form->setSilentValidation();
        $this->form->addClass('filter-form');
        
        $autoAddVars = array(
            Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE,
            Application_Admin_ScreenInterface::REQUEST_PARAM_MODE,
            Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE,
            Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION
        );
        
        foreach($autoAddVars as $varName) {
            $value = $this->request->getParam($varName);
            if(!empty($value)) {
                $this->form->addHiddenVar($varName, $value);
            }
        }
        
        foreach($this->hiddens as $name => $value) {
            $this->form->addHiddenVar($name, $value);
        }
        
        $form = $this->form->getForm();
        
        if($this->isActive()) {
            $form->addClass('filters-active');
        }
        
        $this->form->addHTML(
            '<div class="filters-active-message">'.
            UI::icon()->information() . ' ' .
            t('Note:') . ' ' .
            t('Filtering is active.').
            '</div>'
            );
        
        $this->injectElementsContainer($form);

        // if the "more" feature is enabled, add the closing markup
        $this->addMoreEnd();
        
        $html =
        '<div class="filters-actions btn-toolbar">'.
        '<div class="btn-group">'.
        UI::button(t('Apply'))
        ->setIcon(UI::icon()->filter())
        ->makeSubmit('apply', 'yes')
        ->makeSmall().' '.
        UI::button(t('Reset'))
        ->setIcon(UI::icon()->reset())
        ->makeSubmit('reset', 'yes')
        ->makeSmall().
        '</div>'.
        '</div>';
        
        $this->form->addHTML($html);
        
        if($this->form->isSubmitted() && $this->form->validate()) {
            $this->saveSettings();
        }
    }
    
    /**
     * Renders and returns the HTML markup for the filters.
     * @return string
     */
    public function render() : string
    {
        $this->log('Rendering the HTML markup.');

        $this->ui->addStylesheet('ui-filtersettings.css');
        
        $this->createForm();
        $this->injectJS();
        
        return 
        '<div id="'.$this->jsID.'">'.
            $this->form->renderHorizontal().
        '</div>';
    }
    
    /**
     * Echos the markup for the form.
     */
    public function display() : void
    {
        echo $this->render();
    }
    
    /**
     * Creates and adds an element to the container for
     * a setting, which automatically configures it, so
     * it can be correctly registered clientside as well.
     *
     * @param string $setting
     * @param string $type
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element
     */
    public function addElement(string $setting, string $type, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element
    {
        if(!$container) {
            $container = $this->form->getForm();
        }
        
        $el = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element::class,
            $container->addElement($type, $setting)
        );
        
        $this->addCustomElement($el);
        
        return $el;
    }
    
   /**
    * Adds a select element for the specified filter setting.
    * 
    * @param string $setting
    * @param HTML_QuickForm2_Container $container
    * @return HTML_QuickForm2_Element_Select
    */
    public function addElementSelect(string $setting, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        return ensureType(
            HTML_QuickForm2_Element_Select::class,
            $this->addElement($setting, 'select', $container)
        );
    }

    public function addElementText(string $setting, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el =  $this->addElement($setting, 'text', $container);

        if($el instanceof HTML_QuickForm2_Element_InputText)
        {
            return $el;
        }

        throw new UnexpectedInstanceException(HTML_QuickForm2_Element_InputText::class, $el);
    }
    
   /**
    * Adds a switch (boolean) element.
    * 
    * @param string $setting
    * @param HTML_QuickForm2_Container $container
    * @return HTML_QuickForm2_Element_Switch 
    */
    public function addElementSwitch(string $setting, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Switch
    {
        return ensureType(
            HTML_QuickForm2_Element_Switch::class,
            $this->addElement($setting, 'switch', $container)
        );
    }
    
    /**
     * Adds a previously created form element that has not been
     * created with the {@link addElement()} method, and configures
     * it to work with the filters.
     *
     * @param HTML_QuickForm2_Element $element
     * @return $this
     */
    public function addCustomElement(HTML_QuickForm2_Element $element)
    {
        $setting = $element->getName();
        
        $this->requireSetting($setting);
        
        $element->setId($this->getElementID($setting));
        $element->setLabel($this->getElementLabel($setting));
        
        return $this;
    }
    
    protected function getElementID(string $setting) : string
    {
        $this->requireSetting($setting);
        
        return $this->definitions[$setting]['elementID'];
    }
    
    protected function getElementLabel(string $setting) : string
    {
        $this->requireSetting($setting);
        
        return $this->definitions[$setting]['label'];
    }
    
    protected function requireSetting(string $name) : void
    {
        if(isset($this->definitions[$name])) {
            return;
        }
        
        throw new Application_Exception(
            'Unknown filtering setting',
            sprintf(
                'Unknown setting [%s]. Available settings are: [%s].',
                $name,
                implode(', ', array_keys($this->definitions))
            ),
            self::ERROR_UNKNOWN_SETTING
        );
    }
    
    protected function injectJS() : void
    {
        $this->ui->addJavascript('application/list_filters.js');
        $this->ui->addJavascript('application/list_filters/dialog/save.js');
        $this->ui->addJavascript('application/list_filters/dialog/load.js');
        
        $jsName = $this->getJSName();
        $this->ui->addJavascriptHead(sprintf(
            "%s = new Application_ListFilters(%s, %s, %s);",
            $jsName,
            json_encode($this->id, JSON_THROW_ON_ERROR),
            json_encode($this->jsID, JSON_THROW_ON_ERROR),
            json_encode($this->isActive(), JSON_THROW_ON_ERROR)
        ));
        
        $this->ui->addJavascriptOnload(sprintf('%s.Start()', $jsName));
        
        foreach($this->definitions as $name => $def)
        {
            if(!$this->isSettingEnabled($name)) {
                continue;
            }

            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.RegisterSetting', $jsName),
                $name,
                $def['label'],
                $def['elementID']
            );
        }
        
        $settingName = $this->storageID.'_presets';

        $decoded = $this->user->getArraySetting($settingName);

        foreach($decoded as $id => $preset)
        {
            foreach($this->definitions as $name => $def)
            {
                if(!$this->isSettingEnabled($name))
                {
                    continue;
                }

                if(!isset($preset['settings'][$name])) {
                    $preset['settings'][$name] = $def['default'];
                }
            }
            
            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.RegisterPreset', $jsName),
                $id,
                $preset['label'],
                $preset['settings']
            );
        }
    }
    
    /**
     * Retrieves the name of the clientside javascript variable in
     * which the client object handling these filter settings will
     * be available under.
     *
     * @return string
     */
    public function getJSName()
    {
        return 'lf'.$this->jsID;
    }
    
    /**
     * Checks whether the filter settings are active (if any of
     * the registered settings are not the default value).
     *
     * @return boolean
     */
    public function isActive()
    {
        foreach($this->definitions as $name => $def) {
            if($this->getSetting($name) != $def['default']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Adds a generic search field to the filter settings,
     * complete with information on the fields in which the
     * search is made and more.
     *
     * @param array $searchFields
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element
     */
    protected function addElementSearch($searchFields, HTML_QuickForm2_Container $container, $options=array())
    {
        $options = array_merge(
            array(
                'advanced-search' => true
            ),
            $options
        );
        
        $this->requireSetting('search');
        
        $last = array_pop($searchFields);
        
        $text = $this->addElement('search', 'text', $container);
        $text->addClass('input-block');
        
        if(empty($searchFields)) {
            $fieldsLabel = t(
                'Searches in %1$s.',
                $last
            );
        } else {
            $fieldsLabel = t(
                'Searches in %1$s and %2$s.',
                implode(', ', $searchFields),
                $last
            );
        }
        
        $comment = $fieldsLabel .
        ' ' .
        t('The search is case insensitive.') . ' ' ;
        
        if($options['advanced-search']) {
            $comment .=
            t(
                'You can use the keywords %1$s, %2$s and %3$s to refine your search %4$s.',
                '<code>'.t('AND').'</code>',
                '<code>'.t('OR').'</code>',
                '<code>'.t('NOT').'</code>',
                '(<a href="javascript:void(0);" onclick="'.$this->getJSName().'.DialogSearchExamples()">'.t('examples').'</a>)'
            );
        }
        
        $text->setComment($comment);
        
        return $text;
    }
    
    /**
     * Adds a multiselect element.
     * @param string $setting
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_Multiselect
     */
    public function addMultiselect($setting, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Multiselect
    {
        return ensureType(
            HTML_QuickForm2_Element_Multiselect::class,
            $this->addElement($setting, 'multiselect', $container)
        );
    }
    
   /**
    * Adds a regular select element.
    * @param string $setting
    * @param HTML_QuickForm2_Container $container
    * @return HTML_QuickForm2_Element_Select
    * @deprecated
    */
    public function addSelect($setting, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        return $this->addElementSelect($setting, $container);
    }
    
   /**
    * Resets all filter settings to the default settings.
    */
    public function reset() : void
    {
        $this->settingsLoaded = false;
    }
    
   /**
    * Checks whether a setting with the specified name exists 
    * within the settings configuration.
    * 
    * @param string $name
    * @return boolean
    */
    public function hasSetting(string $name) : bool
    {
        return isset($this->definitions[$name]) && $this->isSettingEnabled($name);
    }
    
    public function addElementDateSearch($setting, HTML_QuickForm2_Container $container=null)
    {
        $date = $this->addElement($setting, 'text', $container);
        $date->setLabel(t('Last modified').'<sup><b class="text-warning">BETA</b></sup>');
        $date->addFilter('trim');
        $date->addClass('input-block');
        $date->setComment(
            t(
                'Specify a date in the form %1$s, or with time included: %2$s (24-hour format).',
                '<code>'.mb_strtolower(t('yyyy-mm-dd')).'</code>',
                '<code>'.mb_strtolower(t('yyyy-mm-dd hh:mm')).'</code>'
                )
            .' '.
            t('You may also use special keywords to extend your search.').
            ' '.
            '<a href="javascript:void(0)" onclick="'.$this->getJSName().'.DialogDateExamples()">'.
                t('More info...').
            '</a>'
        );
    }
    
   /**
    * Adds a country selector element.
    * 
    * @param string $setting The name of the setting to which this should be tied
    * @param HTML_QuickForm2_Container $container
    * @param array $options
    * @return HTML_QuickForm2_Element_Select
    */
    public function addElementCountry($setting, HTML_QuickForm2_Container $container=null, $options=array())
    {
        if(!is_array($options)) {
            $options = array();
        }
        
        $options = array_merge(
            array(
                'with-invariant' => true,
                'please-select' => true
            ),
            $options
        );
        
        $el = $this->addSelect($setting, $container);

        if($options['please-select']) {
            $el->addOption(t('Please select...'), '');
        }
        
        $collection = Application_Driver::createCountries();
        $countries = $collection->getAll();
        
        foreach($countries as $country) 
        {
            if($country->isCountryIndependent() && !$options['with-invariant']) {
                continue;
            }
            
            $el->addOption(
                $country->getLocalizedLabel(), 
                (string)$country->getID()
            );
        }
        
        return $el;
    }
    
   /**
    * Attempts to retrieve the country selected in a country
    * setting. Must have been added using {@link addElementCountry()}.
    * 
    * @param string $name The name of the setting 
    * @return Application_Countries_Country|NULL
    */
    public function getSettingCountry(string $name) : ?Application_Countries_Country
    {
        $countries = Application_Driver::createCountries();
        
        $value = intval($this->getSetting($name));
        
        if(!empty($value) && $countries->idExists($value)) {
            return $countries->getByID($value);
        }
        
        return null;
    }
    
    public function getJSSubmitHandler()
    {
        return $this->getJSName().'.Submit()';
    }
    
    protected $hasMore = false;
    
    public function addMore(HTML_QuickForm2_Container $container=null)
    {
        if($this->hasMore) {
            throw new Application_Exception(
                'Only one more element may be added.',
                sprintf(
                    'The filter settings [%s] can only accept one more element.',
                    get_class($this)
                ),
                self::ERROR_ONLY_ONE_MORE_ALLOWED
            );
        }
        
        if(!$container) {
            $container = $this->form->getForm();
        }
        
        $this->hasMore = true;
        
        $tpl = $this->ui->getPage()->createTemplate('filtersettings/more-start.php');
        $tpl->setVar('settings', $this);
        
        $el = $this->form->addHTML($tpl->render());
        
        return $el;
    }

    protected function addMoreEnd()
    {
        if(!$this->hasMore) {
            return;
        }
        
        $tpl = $this->ui->getPage()->createTemplate('filtersettings/more-end');
        $tpl->setVar('settings', $this);
        
        $this->form->addHTML($tpl->render());
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    protected ?string $cachedLogIdentifier = null;

    public function getLogIdentifier() : string
    {
        if(!isset($this->cachedLogIdentifier))
        {
            $this->cachedLogIdentifier = sprintf(
                'FilterSettings [%s] ID [%s]',
                ClassHelper::getClassTypeName($this),
                $this->getID()
            );
        }

        return $this->cachedLogIdentifier;
    }
}