<?php
/**
 * File containing the {@link Application_FilterSettings} class.
 *
 * @package Application
 * @subpackage Filtering
 * @see Application_FilterSettings
 */

/**
 * Base class for custom filter setting implementations. This can
 * be used to create a settings form intended to configure a filter
 * criteria instance. The storage of the settings is handled
 * automatically on a per user basis.
 *
 * Usage:
 
 * - Extend this class, and implement the abstract methods
 * - Instantiate an instance of the class
 * - Add it to a sidebar using the {@link UI_Page_Sidebar::addFilterSettings()} method
 * - Configure your filter criteria using the {@link Application_FilterSettings::configureFilters()} method
 *
 * @package Application
 * @subpackage Filtering
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterSettings
{
    const ERROR_UNKNOWN_SETTING = 450001;
    const ERROR_NO_SETTINGS_REGISTERED = 450002;
    const ERROR_MISSING_AUTOCONFIG_METHOD = 450003;
    const ERROR_ONLY_ONE_MORE_ALLOWED = 450004;

   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var string
    */
    protected $jsID;
    
    /**
     * @var Application_Request
     */
    protected $request;
    
    /**
     * @var Application_User
     */
    protected $user;
    
    protected $settings;
    
    protected $definitions;

    /**
     * @var array<string,mixed>
     */
    protected $defaultSettings;
    
    protected $autoConfigure = array();
    
   /**
    * Available during the injectElements method.
    * @var HTML_QuickForm2_Container
    */
    protected $container;
    
    /**
     * @var UI
     */
    protected $ui;
    
   /**
    * @var array<string,string|number|bool>
    */
    protected $hiddens = array();
    
    public function __construct(string $id)
    {
        if(empty($id))
        {
            $id = 'filter_settings_'.$id;
        }
        
        $this->id = $id;
        $this->jsID = nextJSID();
        $this->user = Application::getUser();
        $this->request = Application_Driver::getInstance()->getRequest();
        $this->ui = UI::getInstance();
        
        $this->registerSettings();
        
        if(empty($this->definitions)) {
            throw new Application_Exception(
                'No settings registered',
                'The [registerSettings] method has been implemented, but it does not register any settings.',
                self::ERROR_NO_SETTINGS_REGISTERED
            );
        }
        
        $this->loadSettings();
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    public function addHiddenVars($vars)
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }
        
        return $this;
    }
    
    /**
     * Adds a hidden var to the filter form.
     * @param string $name
     * @param string $value
     * @return Application_FilterSettings
     */
    public function addHiddenVar($name, $value)
    {
        $this->hiddens[$name] = $value;
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
        $this->container = $container;
        
        $this->injectElements($container);
        
        $names = array_keys($this->settings);
        
        foreach($names as $name)
        {
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
    public function configureFilters(Application_FilterCriteria $filters)
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
    protected function addAutoConfigure($settingName)
    {
        $method = 'autoConfigure_'.$settingName;
        if(method_exists($this, $method)) {
            if(!in_array($method, $this->autoConfigure)) {
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
     * @param string $label
     * @param mixed $default
     */
    protected function registerSetting($name, $label, $default=null)
    {
        $this->defaultSettings[$name] = $default;
        
        $this->definitions[$name] = array(
            'label' => $label,
            'default' => $default,
            'elementID' => $this->jsID.'-'.$name
        );
    }
    
    /**
     * Loads the currently stored settings.
     */
    protected function loadSettings()
    {
        $settings = $this->defaultSettings;
        $stored = $this->user->getSetting($this->id);
        if(!empty($stored)) {
            $stored = @json_decode($stored, true);
        }
        
        if($stored) {
            $names = array_keys($settings);
            foreach ($names as $name) {
                if(isset($stored[$name])) {
                    $settings[$name] = $stored[$name];
                }
            }
        }
        
        $this->settings = $settings;
        
        $this->handle_settingsLoaded();
    }
    
    protected function handle_settingsLoaded()
    {
        
    }
    
    /**
     * Saves the settings when the user has submitted the
     * filters form.
     */
    protected function saveSettings()
    {
        $values = $this->form->getValues();
        
        if($this->request->getParam('reset') == 'yes') {
            $values = $this->defaultSettings;
        }
        
        // remove all unneeded request parameters to avoid them
        // being present in the refresh URL.
        $toRemove = array_merge(array('reset', 'apply', $this->form->getTrackingName()), array_keys($this->definitions));
        $this->request->removeParams($toRemove);
        
        $url = $this->request->buildRefreshURL();
        
        $this->settings = $values;
        
        $this->user->setArraySetting($this->id, $values);
        $this->user->saveSettings();
        
        
        Application_Driver::getInstance()->redirectTo($url);
    }
    
    /**
     * Retrieves all settings as an associative array with
     * setting name > value pairs.
     *
     * @return array
     */
    public function getSettings()
    {
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
     * @return string|array|NULL
     */
    public function getSetting($name)
    {
        $this->requireSetting($name);
        
        if(isset($this->settings[$name])) {
            return $this->settings[$name];
        }
        
        return $this->definitions[$name]['default'];
    }
    
    public function getArraySetting($name)
    {
        $value = $this->getSetting($name);
        if(!empty($value) && is_array($value)) {
            return $value;
        }
        
        return array();
    }
    
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
        return $this;
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
        $this->form = $this->ui->createForm($this->id, $this->getSettings());
        $this->form->makeCondensed();
        $this->form->setSilentValidation();
        $this->form->addClass('filter-form');
        
        $autoAddVars = array(
            'page',
            'mode',
            'submode',
            'action'
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
    public function render()
    {
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
    public function display()
    {
        echo $this->render();
    }
    
    /**
     * Creates and adds an element to the container for
     * a setting, which automatically configures it so
     * it can be correctly registered clientside as well.
     *
     * @param string $setting
     * @param string $type
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element
     */
    public function addElement(string $setting, string $type, HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element
    {
        if(!$container) {
            $container = $this->form->getForm();
        }
        
        $el = ensureType(
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
    
    protected function injectJS()
    {
        $this->ui->addJavascript('application/list_filters.js');
        $this->ui->addJavascript('application/list_filters/dialog/save.js');
        $this->ui->addJavascript('application/list_filters/dialog/load.js');
        
        $jsName = $this->getJSName();
        $this->ui->addJavascriptHead(sprintf(
            "%s = new Application_ListFilters(%s, %s, %s);",
            $jsName,
            json_encode($this->id),
            json_encode($this->jsID),
            json_encode($this->isActive())
        ));
        
        $this->ui->addJavascriptOnload(sprintf('%s.Start()', $jsName));
        
        foreach($this->definitions as $name => $def) {
            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.RegisterSetting', $jsName),
                $name,
                $def['label'],
                $def['elementID']
            );
        }
        
        $settingName = $this->id.'_presets';

        $decoded = $this->user->getArraySetting($settingName);

        foreach($decoded as $id => $preset) {
            foreach($this->definitions as $name => $def) {
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
        $this->settings = $this->defaultSettings;
    }
    
   /**
    * Checks whether a setting with the specified name exists 
    * within the settings configuration.
    * 
    * @param string $name
    * @return boolean
    */
    public function hasSetting($name)
    {
        return isset($this->definitions[$name]);
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
}