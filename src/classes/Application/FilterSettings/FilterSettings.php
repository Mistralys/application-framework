<?php
/**
 * @package Application
 * @subpackage Filtering
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Driver\DriverException;
use Application\FilterSettings\FilterSettingsException;
use Application\FilterSettings\SettingDef;
use Application\FilterSettingsInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\FilterCriteriaInterface;
use Application\Traits\HiddenVariablesTrait;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Interfaces\StringableInterface;

abstract class Application_FilterSettings
    implements FilterSettingsInterface
{
    use Application_Traits_Loggable;
    use UI_Traits_RenderableGeneric;
    use HiddenVariablesTrait;

    public const int ERROR_UNKNOWN_SETTING = 450001;
    public const int ERROR_NO_SETTINGS_REGISTERED = 450002;
    public const int ERROR_ONLY_ONE_MORE_ALLOWED = 450004;
    public const int ERROR_MISSING_ID = 450005;
    public const int ERROR_SETTING_ALREADY_REGISTERED = 450006;

    public const string SETTING_PREFIX = 'filter_settings_';
    public const string REQUEST_VAR_RESET = 'reset';
    public const string REQUEST_VAR_APPLY = 'apply';

    protected string $id;
    protected string $jsID;
    protected Application_Request $request;
    protected Application_User $user;

    /**
     * @var array<string,string|number|array<int|string,mixed>|bool|NULL>
     */
    private array $settings = array();

    /**
     * @var array<string,SettingDef>
     */
    protected array $definitions = array();

   /**
    * Available during the injectElements method.
    * @var HTML_QuickForm2_Container
    */
    protected HTML_QuickForm2_Container $container;
    
    protected UI $ui;
    
    protected string $storageID;
    private bool $settingsLoaded = false;

    /**
     * @param string $id The ID is used to persist the settings.
     *
     * @throws FilterSettingsException
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
            throw new FilterSettingsException(
                'No settings registered',
                'The [registerSettings] method has been implemented, but it does not register any settings.',
                self::ERROR_NO_SETTINGS_REGISTERED
            );
        }
        
        $this->init();
    }

    public function setID(string $id) : self
    {
        if(empty($id))
        {
            throw new FilterSettingsException(
                'An empty ID for filter settings is not allowed.',
                '',
                self::ERROR_MISSING_ID
            );
        }

        $this->log('Setting ID to [%s].', $id);

        $this->id = $id;
        $this->storageID = self::SETTING_PREFIX.$id;
        $this->settingsLoaded = false;

        $this->logIdentifier = sprintf(
            'FilterSettings [%s] | [#%s]',
            ClassHelper::getClassTypeName($this),
            $this->getID()
        );

        return $this;
    }
    
    protected function init() : void
    {
        
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
    * NOTE: Legacy method.
    * The new way is to set the form element injection callback with
    * the {@see SettingDef::setInjectCallback()} method.
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

    public const string SETTING_DEFAULT_SEARCH = 'search';

    private bool $searchConfigured = false;

    /**
     * Automatically configures search terms in the filters
     * if a search setting is present. Meant to be used during
     * the <code>_configureFilters()</code> method.
     *
     * @param string|SettingDef|NULL $setting Defaults to {@see self::SETTING_DEFAULT_SEARCH}.
     */
    protected function configureSearch(string|SettingDef|NULL $setting=null) : void
    {
        if($this->searchConfigured) {
            return;
        }

        $this->searchConfigured = true;

        $search = trim($this->getSearchSetting($setting)->getValue()->getString());
        
        if(strlen($search) >= 2) 
        {
            $this->filters->setSearch($search);
        }
    }

    /**
     * Registers the generic search field that uses the collection's
     * configured search fields list in the UI.
     *
     * See the {@link inject_search()} method in case you wish to
     * adjust the search field's appearance.
     *
     * The rest is automatic.
     *
     * @param string|null $setting Defaults to {@see self::SETTING_DEFAULT_SEARCH}.
     * @param string|null $label Defaults to "Search".
     * @return void
     */
    protected function registerSearchSetting(?string $setting=null, ?string $label=null) : void
    {
        if(empty($label)) {
            $label = t('Search');
        }

        if(empty($setting)) {
            $setting = self::SETTING_DEFAULT_SEARCH;
        }

        $this->registerSetting($setting, $label)
            ->setInjectCallback($this->inject_search(...))
            ->setConfigureCallback($this->configureSearch(...));
    }

    protected function inject_search() : HTML_QuickForm2_Element_InputText
    {
        return $this->addElementSearch(array('Full text'));
    }

    public function getSearchSetting($name=null) : SettingDef
    {
        if(empty($name)) {
            $name = self::SETTING_DEFAULT_SEARCH;
        }

        if($name instanceof SettingDef) {
            $name = $name->getName();
        }

        return $this->requireSetting($name);
    }
    
    protected function injectElementsContainer(HTML_QuickForm2_Container $container): void
    {
        $this->loadSettings();

        $this->container = $container;
        
        $this->injectElements($container);
        
        $names = array_keys($this->definitions);
        
        foreach($names as $name)
        {
            $setting = $this->requireSetting($name);

            if(!$setting->isEnabled()) {
                continue;
            }

            $setting->inject();
        }
    }
    
    protected FilterCriteriaInterface $filters;

    public function configureFilters(FilterCriteriaInterface $filters) : void
    {
        $this->filters = $filters;

        foreach($this->definitions as $def) {
            $def->configure($filters);
        }

        $this->_configureFilters();
    }
    
    /**
     * Registers a setting handled by the settings.
     *
     * @param string $name
     * @param string|int|float|StringableInterface|NULL $label
     * @param mixed $default
     * @param class-string|NULL $customClass
     * @return SettingDef
     * @throws UI_Exception
     */
    protected function registerSetting(string $name, string|int|float|StringableInterface|NULL $label, mixed $default=null, ?string $customClass=null) : SettingDef
    {
        $this->log('RegisterSettings | Registered setting [%s].', $name);

        if(isset($this->definitions[$name])) {
            throw new UI_Exception(
                'Filter setting already registered',
                sprintf(
                    'The filter setting [%s] has already been registered.',
                    $name
                ),
                self::ERROR_SETTING_ALREADY_REGISTERED
            );
        }

        if($customClass === null) {
            $customClass = SettingDef::class;
        }

        $def = ClassHelper::requireObjectInstanceOf(
            SettingDef::class,
            new $customClass($this, $name, $label, $default)
        );

        // Legacy setting support with injection methods in the filter class
        $method = 'inject_'.str_replace('-', '_', $name);
        if(method_exists($this, $method)) {
            $def->setInjectCallback($this->$method(...));
        }

        $this->definitions[$name] = $def;

        return $def;
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

        $json = $this->user->getSetting($this->storageID);
        $data = array();
        $settings = array();

        if (!empty($json))
        {
            $data = JSONConverter::json2arraySilent($json);
            $this->log('LoadSettings | Found [%s] settings.', count($data));
        }
        else
        {
            $this->log('LoadSettings | None found: Empty, or could not be decoded.');
        }

        foreach ($this->definitions as $name => $def)
        {
            $settings[$name] = $data[$name] ?? $def->getDefault();
        }

        $this->settings = $settings;

        $this->log(
            'LoadSettings | Calling post-load handler method [%s].',
            array($this, 'handle_settingsLoaded')[1]
        );

        $this->handle_settingsLoaded();
    }
    
    protected function handle_settingsLoaded() : void
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

        if($this->request->getBool(self::REQUEST_VAR_RESET)) {
            $values = $this->getDefaultSettings();
        }
        
        // remove all unneeded request parameters to avoid them
        // being present in the refresh URL.
        $toRemove = array_merge(array(self::REQUEST_VAR_RESET, self::REQUEST_VAR_APPLY, $this->form->getTrackingName()), array_keys($this->definitions));
        $this->request->removeParams($toRemove);
        
        $url = $this->request->buildRefreshURL();
        
        $this->settings = $values;
        
        $this->user->setArraySetting($this->storageID, $values);
        $this->user->saveSettings();

        Application_Driver::getInstance()->redirectTo($url);
    }

    protected function getDefaultSettings() : array
    {
        return array_map(
            static function ($def) {
                return $def->getDefault();
            },
            $this->definitions
        );
    }

    // region: Access setting values

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

    public function getSetting(string $name)
    {
        $setting = $this->requireSetting($name);

        $this->loadSettings();

        return $this->settings[$name] ?? $setting->getDefault();
    }

    public function getArraySetting(string $name) : array
    {
        return $this->getSettingArray($name);
    }

    public function getSettingArray(string $name) : array
    {
        $value = $this->getSetting($name);
        if(is_array($value)) {
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

    public function setSetting(string $name, $value) : self
    {
        $this->loadSettings();

        $this->settings[$name] = $value;
        return $this;
    }

    /**
     * @param array<string,scalar|array>|null $settings
     * @return $this
     */
    public function setSettings(?array $settings) : self
    {
        if($settings === null) {
            return $this;
        }

        $this->loadSettings();

        $this->settings = array_merge($this->settings, $settings);

        return $this;
    }

    /**
     * @param string $name
     * @param bool $enabled
     * @return $this
     * @throws Application_Exception
     */
    public function setSettingEnabled(string $name, bool $enabled) : self
    {
        $this->requireSetting($name)->setEnabled($enabled);
        return $this;
    }

    public function isSettingEnabled(string $name) : bool
    {
        return $this->requireSetting($name)->isEnabled();
    }
    
    protected UI_Form $form;
    
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
            AdminScreenInterface::REQUEST_PARAM_PAGE,
            AdminScreenInterface::REQUEST_PARAM_MODE,
            AdminScreenInterface::REQUEST_PARAM_SUBMODE,
            AdminScreenInterface::REQUEST_PARAM_ACTION
        );
        
        foreach($autoAddVars as $varName) {
            $value = $this->request->getParam($varName);
            if(!empty($value)) {
                $this->form->addHiddenVar($varName, $value);
            }
        }
        
        foreach($this->getHiddenVars() as $name => $value) {
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
                    ->makeSubmit(self::REQUEST_VAR_APPLY, 'yes')
                    ->makeSmall().' '.
                UI::button(t('Reset'))
                    ->setIcon(UI::icon()->reset())
                    ->makeSubmit(self::REQUEST_VAR_RESET, 'yes')
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

    // region: Form elements

    public function addMore(?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        if($this->hasMore) {
            throw new FilterSettingsException(
                'Only one more element may be added.',
                sprintf(
                    'The filter settings [%s] can only accept one mor^e element.',
                    get_class($this)
                ),
                self::ERROR_ONLY_ONE_MORE_ALLOWED
            );
        }

        if($container === null) {
            $container = $this->form->getForm();
        }

        $this->hasMore = true;

        $tpl = $this->ui->getPage()->createTemplate('filtersettings/more-start.php');
        $tpl->setVar('settings', $this);

        return $this->form->addHTML($tpl->render(), $container);
    }

    public function addMultiselect(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Multiselect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Multiselect::class,
            $this->addElement($setting, 'multiselect', $container)
        );
    }

    public function addSelect(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        return $this->addElementSelect($setting, $container);
    }

    /**
     * Adds a generic search field to the filter settings,
     * complete with information on the fields in which the
     * search is made and more.
     *
     * @param string[] $searchFields Human-readable field labels.
     * @param HTML_QuickForm2_Container|NULL $container
     * @param array<string,mixed> $options
     * @return HTML_QuickForm2_Element_InputText
     * @throws Application_Exception
     * @throws BaseClassHelperException
     */
    protected function addElementSearch(array $searchFields, ?HTML_QuickForm2_Container $container=null, array $options=array()) : HTML_QuickForm2_Element_InputText
    {
        $options = array_merge(
            array(
                'advanced-search' => true
            ),
            $options
        );

        $this->requireSetting('search');

        $last = array_pop($searchFields);

        $text = $this->addElementText('search', $container);
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

    public function addElementDateSearch(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText($setting, $container);
        $el->setLabel(t('Last modified').'<sup><b class="text-warning">BETA</b></sup>');
        $el->addFilter('trim');
        $el->addClass('input-block');
        $el->setComment(
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

        return $el;
    }

    public function addElementCountry(string $setting, ?HTML_QuickForm2_Container $container=null, array $options=array()) : HTML_QuickForm2_Element_Select
    {
        $options = array_merge(
            array(
                'with-invariant' => true,
                'please-select' => true
            ),
            $options
        );

        $el = $this->addElementSelect($setting, $container);

        if($options['please-select']) {
            $el->addOption(t('Please select...'), '');
        }

        $collection = AppFactory::createCountries();
        $countries = $collection->getAll();

        foreach($countries as $country)
        {
            if( $options['with-invariant'] !== false && $country->isCountryIndependent()) {
                continue;
            }

            $el->addOption(
                $country->getLocalizedLabel(),
                (string)$country->getID()
            );
        }

        return $el;
    }

    public function addElement(string $setting, string $type, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element
    {
        if($container === null) {
            $container = $this->form->getForm();
        }
        
        $el = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element::class,
            $container->addElement($type, $setting)
        );
        
        $this->addCustomElement($el);
        
        return $el;
    }

    public function addElementSelect(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Select::class,
            $this->addElement($setting, 'select', $container)
        );
    }

    public function addElementText(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_InputText::class,
            $this->addElement($setting, 'text', $container)
        );
    }

    public function addElementSwitch(string $setting, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Switch
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Switch::class,
            $this->addElement($setting, HTML_QuickForm2_Element_Switch::ELEMENT_TYPE, $container)
        );
    }

    public function addCustomElement(HTML_QuickForm2_Element $element) : self
    {
        $setting = $this->requireSetting($element->getName());
        
        $element->setId($setting->getElementID());
        $element->setLabel($setting->getLabel());
        
        return $this;
    }

    // endregion
    
    protected function requireSetting(string $name) : SettingDef
    {
        if(isset($this->definitions[$name])) {
            return $this->definitions[$name];
        }
        
        throw new FilterSettingsException(
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
                $def->getLabel(),
                $def->getElementID()
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
                    $preset['settings'][$name] = $def->getDefault();
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

    public function getJSID() : string
    {
        return $this->jsID;
    }

    public function getJSName() : string
    {
        return 'lf'.$this->jsID;
    }

    public function isActive() : bool
    {
        foreach($this->definitions as $name => $def)
        {
            $value = $this->nullify($this->getSetting($name));
            $default = $this->nullify($def->getDefault());

            if($value !== $default) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts empty string values to NULL to facilitate comparisons.
     *
     * @param mixed|NULL $value
     * @return mixed|null
     */
    private function nullify(mixed $value) : mixed
    {
        if($value === '' || $value === null) {
            return null;
        }

        return $value;
    }

    public function reset() : void
    {
        $this->settingsLoaded = false;
    }

    public function hasSetting(string $name) : bool
    {
        return isset($this->definitions[$name]) && $this->isSettingEnabled($name);
    }


    public function getSettingCountry(string $name) : ?Application_Countries_Country
    {
        $countries = AppFactory::createCountries();
        
        $value = (int)$this->getSetting($name);
        
        if(!empty($value) && $countries->idExists($value)) {
            return $countries->getByID($value);
        }
        
        return null;
    }
    
    public function getJSSubmitHandler() : string
    {
        return $this->getJSName().'.Submit()';
    }
    
    protected bool $hasMore = false;

    /**
     * @return $this
     * @throws BaseClassHelperException
     * @throws UI_Themes_Exception
     */
    protected function addMoreEnd() : self
    {
        if(!$this->hasMore) {
            return $this;
        }
        
        $tpl = $this->ui->getPage()->createTemplate('filtersettings/more-end');
        $tpl->setVar('settings', $this);
        
        $this->form->addHTML($tpl->render());

        return $this;
    }

    public function getID() : string
    {
        return $this->id;
    }

    protected string $logIdentifier = 'FilterSettings (Uninitialized)';

    public function getLogIdentifier() : string
    {
        return $this->logIdentifier;
    }
}