<?php
/**
 * File containing the {@see UI_Form} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form
 */

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\Interface_Stringable;
use AppUtils\RegexHelper;
use function AppUtils\parseVariable;

/**
 * Form handling class used to create form elements, as well as
 * rules and utilities all around the elements.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Form extends UI_Renderable
{
    public const ERROR_DUPLICATE_ELEMENT_ID = 45524001;
    public const ERROR_INVALID_FORM_DATA = 45524002;
    public const ERROR_UNKNOWN_REGEX_HINT = 45524003;
    public const ERROR_UNKNOWN_EVENT_HANDLER = 45524004;
    public const ERROR_INVALID_EVENT_HANDLER = 45524005;
    public const ERROR_INVALID_LENGTH_LIMIT = 45524006;
    public const ERROR_ELEMENT_HAS_NO_ID = 45524007;
    public const ERROR_MINMAX_VALUES_EMPTY = 45524008;
    public const ERROR_INVALID_MINMAX_VALUES = 45524009;
    public const ERROR_MINMAX_VALUES_NOT_A_NUMBER = 45524010;
    public const ERROR_OBSOLETE_IMAGE_ELEMENT = 45524011;
    public const ERROR_INVALID_RENDER_CALLBACK = 45524012;
    public const ERROR_UNHANDLED_SUBMIT_HANDLER_SUBJECT = 45524013;
    public const ERROR_INVALID_FORM_RENDERER = 45524014;
    public const ERROR_INVALID_DATEPICKER_ELEMENT = 45524015;
    public const ERROR_CANNOT_CREATE_ELEMENT = 45524016;

    /**
     * Stores the string that form element IDs get prefixed with.
     * @var string
     */
    public const ID_PREFIX = 'f-';

    public const REL_BUTTON = 'Button';
    public const REL_LAYOUT_LESS_GROUP = 'LayoutlessGroup';
    public const FORM_PREFIX = 'form-';
    public const ELEMENT_TYPE_DATE_PICKER = 'datepicker';

    protected string $id;
    protected HTML_QuickForm2 $form;
    protected HTML_QuickForm2_DataSource_Array $defaultDataSource;

    /**
     * Creates a new form. Use the {@link getForm()} method to configure
     * the QuickForm object.
     *
     * @param UI $ui
     * @param string $formID
     * @param array<string,mixed> $defaultData
     */
    public function __construct(UI $ui, string $formID, string $method, array $defaultData = array())
    {
        parent::__construct($ui->getPage());
        
        $this->registerCustomElements();
        $this->registerCustomRules();

        if(!is_array($defaultData)) {
            throw new Application_Exception(
                'Invalid form data',
                sprintf(
                    'The default form data must be an array, [%s] given.',
                    gettype($defaultData)        
                ),
                self::ERROR_INVALID_FORM_DATA
            );
        }
        
        $this->id = $formID;
        $this->defaultDataSource = new HTML_QuickForm2_DataSource_Array($defaultData);
        $this->form = new HTML_QuickForm2(self::FORM_PREFIX . $formID, $method);
        $this->form->addDataSource($this->defaultDataSource);
        $this->form->setAttribute('data-jsid', $formID);
        
        $this->form->getEventHandler()->onNodeAdded(array($this, 'callback_onNodeAdded'));

        if($ui->hasPage()) {
            $this->addHiddenVar('page', $this->ui->getPage()->getID());
        }
        
        $this->ui->addJavascriptOnload('FormHelper.init()');
        
        self::$instances[] = $this;
    }
    
    public function getID() : string
    {
        return $this->form->getId();
    }
    
    public function getJSID() : string
    {
        return $this->form->getAttribute('data-jsid');
    }
    
    public function callback_onNodeAdded(HTML_QuickForm2_Event_NodeAdded $event)
    {
        $node = $event->getNode();
        
        // Adds the filter to strip control characters to all text elements.
        // @see CISOCMS-422
        if($node instanceof HTML_QuickForm2_Element_Textarea || $node instanceof HTML_QuickForm2_Element_InputText) 
        {
            $node->addFilter(array('AppUtils\ConvertHelper', 'stripControlCharacters'));
            $node->setAttribute('data-strip-controlchars', 'yes');
        }
    }
    
    protected static $customElementsRegistered = false;
    
   /**
    * Registers all available custom form elements with
    * QuickForm2: source folders are the elements bundled
    * with the Form manager (UI/Form/Element) as well as
    * those specific to the application (DriverName/FormElements).
    */
    protected function registerCustomElements() : void
    {
        if(self::$customElementsRegistered === true) {
            return;
        }
        
        self::$customElementsRegistered = true;
        
        $driver = Application_Driver::getInstance();
        $app = $driver->getApplication();
        
        $folders = array(
            $app->getClassesFolder().'/UI/Form/Element',
            $driver->getClassesFolder().'/FormElements'
        );
        
        foreach($folders as $folder) 
        {
            if(!is_dir($folder)) {
                continue;
            }
            
            $names = FileHelper::createFileFinder($folder)
                ->getPHPClassNames();
            
            foreach($names as $name) 
            {
                $id = strtolower($name);
                $this->log(sprintf('Registering custom form element [%s].', $id));
                $this->registerCustomElement($id, $name);
            }
        }
    }
    
   /**
    * Registers all custom form rules with QuickForm2:
    * source folders are the rules bundled with the 
    * Form manager (UI/Form/Rule) as well as those 
    * specific to the application (DriverName/FormRules).
    */
    protected function registerCustomRules() : void
    {
        $driver = Application_Driver::getInstance();
        $app = $driver->getApplication();
        
        $folders = array(
            $app->getClassesFolder().'/UI/Form/Rule',
            $driver->getClassesFolder().'/FormRules'
        );
        
        foreach($folders as $folder)
        {
            if(!is_dir($folder)) {
                continue;
            }
            
            $names = FileHelper::createFileFinder($folder)
                ->getPHPClassNames();
            
            foreach($names as $name) 
            {
                $id = strtolower($name);
                $this->log(sprintf('Registering custom form rule [%s].', $id));
                $this->registerCustomRule($id, $name);
            }
        }
    }
    
    protected function log(string $message) : void
    {
        Application::log('Form Manager | '.$message);
    }
    
   /**
    * @var UI_Form[]
    */
    protected static array $instances = array();



    /**
     * Registers a custom form element rule.
     *
     * Example:
     *
     * registerCustomRule('rule_alias', 'RuleName');
     *
     * This would register an element that can be added to
     * a form using addRule('rule_alias'). The class for
     * it has to be called HTML_QuickForm2_Rule_RuleName.
     *
     * @param string $alias
     * @param string $ruleName
     */
    public function registerCustomRule(string $alias, string $ruleName) : void
    {
        HTML_QuickForm2_Factory::registerRule(
            $alias,
            HTML_QuickForm2_Rule::class . '_'.$ruleName
        );
    }

    /**
     * @var array<string,array{alias:string,name:string}>
     */
    protected array $customElements = array();
    
    /**
     * Registers a custom form Element class.
     *
     * Example:
     *
     * registerCustomElement('element_alias', 'ElementName');
     *
     * This would register an element that can be added to
     * a form using addElement('element_alias'). The class
     * for it has to be called HTML_QuickForm2_Element_ElementName.
     *
     * @param string $alias
     * @param string $elementName
     */
    public function registerCustomElement(string $alias, string $elementName) : void
    {
        $this->customElements[$alias] = array(
            'alias' => $alias,
            'name' => $elementName
        );
        
        HTML_QuickForm2_Factory::registerElement(
            $alias,
            HTML_QuickForm2_Element::class . '_' . $elementName
        );
    }
    
   /**
    * Retrieves a list of all registered custom elements.
    * @return array Indexed array with these keys in each entry: "alias", "name" and "file"
    */
    public function getCustomElements() : array
    {
        return array_values($this->customElements);
    }

    /**
     * Retrieves the default data source of the form, which
     * is used to set the default values of elements.
     *
     * @return HTML_QuickForm2_DataSource_Array
     */
    public function getDefaultDataSource() : HTML_QuickForm2_DataSource_Array
    {
        return $this->defaultDataSource;
    }

    /**
     * Sets a default form value. Note that this MUST be in
     * array form, even for single elements. Example:
     *
     * array(
     *     'element_name' => 'value',
     *     'group_name' => array(
     *         'grouped_element_name' => 'value'
     *     )
     * )
     * @param array $value
     */
    public function setDefaultValue($value)
    {
        $this->defaultDataSource->setValues($value);
    }
    
    public function setDefaultValues($values)
    {
        $this->log('Overwriting existing default values.');

        $this->defaultDataSource->setValues($values);
    }

    /**
     * @var array<string,HTML_QuickForm2_Element_InputHidden>
     */
    protected array $hiddens = array();

    /**
     * Selects the default element in the form. If possible, when the page is
     * loaded the field will automatically get focus.
     *
     * @param HTML_QuickForm2_Node $element
     */
    public function setDefaultElement(HTML_QuickForm2_Node $element)
    {
        $this->ui->addJavascriptOnload("application.focusField('" . $element->getId() . "')");
    }

   /**
    * Sets an attribute of the form element itself.
    * 
    * @param string $name
    * @param string|number|NULL $value
    * @return UI_Form
    */
    public function setAttribute(string $name, $value) : self
    {
        $this->form->setAttribute($name, $value);
        return $this;
    }

   /**
    * Adds a class to the form tag itself.
    * 
    * @param string $className
    * @return UI_Form
    */
    public function addClass(string $className) : self
    {
        $this->form->addClass($className);
        return $this;
    }
    
    public function removeClass(string $className) : self
    {
        $this->form->removeClass($className);
        return $this;
    }

    /**
     * Returns an element if its id is found
     *
     * @param string $id Element id to search for
     * @return HTML_QuickForm2_Node|null
     */
    public function getElementByID(string $id) : ?HTML_QuickForm2_Node
    {
        return $this->form->getElementById($id);
    }

   /**
    * Retrieves the first element in the form whose name
    * matches the specified name.
    * 
    * @param string $name
    * @return HTML_QuickForm2_Element|null
    */
    public function getElementByName(string $name) : ?HTML_QuickForm2_Element
    {
        $elements = $this->form->getElementsByName($name);
        
        if(!empty($elements)) 
        {
            return ensureType(
                HTML_QuickForm2_Element::class,
                $elements[0]
            );
        }
        
        return null;
    }

    public function getValue($elementID)
    {
        $element = $this->getElementByID($elementID);
        if ($element) {
            return $element->getValue();
        }

        return null;
    }

    /**
     * Sets the checked attribute of an element by its ID
     * @param string $elementID
     */
    public function setChecked($elementID)
    {
        $element = $this->form->getElementById($elementID);
        if(!$element) {
            return false;
    }

        $element->setAttribute('checked', 'checked');
        return true;
    }

    /**
     * Checks whether the form has been submitted.
     * @return boolean
     */
    public function isSubmitted() : bool
    {
        return $this->form->isSubmitted();
    }

    /**
     * @return HTML_QuickForm2
     */
    public function getForm() : HTML_QuickForm2
    {
        return $this->form;
    }
    
   /**
    * Retrieves all required elements in the form, or the 
    * specified container if the first parameter is set.
    * 
    * @param HTML_QuickForm2_Container|NULL $container
    * @param array $result
    * @return HTML_QuickForm2_Node[]
    */
    public function getRequiredElements(HTML_QuickForm2_Container $container=null, $result=array())
    {
        if(!$container) {
            $container = $this->form;
        }
        
        $elements = $container->getElements();
        
        foreach($elements as $element) 
        {
            if($element->isRequired() || $element->getAttribute('data-required') === 'true')
            {
                $result[] = $element;
                continue;
            }
            
            if($element instanceof HTML_QuickForm2_Container) 
            {
                $result = $this->getRequiredElements($element);
            }
        }
        
        return $result;
    }

    protected $silentValidation = false;
    
    public function setSilentValidation()
    {
        $this->silentValidation = true;
    }
    
   /**
    * Simulates the form being submitted using the form's current
    * values. This can be used to validate an arbitrary set of values
    * without needing to submit an actual form mask.
    * 
    * To use this, create a form with the values you wish to validate
    * as default values, then validate the form as per usual. 
    */
    public function simulateSubmit()
    {
        $this->setSilentValidation();
        
        $values = $this->getValues();
        
        $values[$this->form->getName()] = 'true';
        
        foreach($values as $varName => $value) {
            $_POST[$varName] = $value;
        }
        
        // the presence of this datasource tells the form that it
        // has been submitted.
        $ds = new HTML_QuickForm2_DataSource_SuperGlobal();
        $this->form->addDataSource($ds);
    }
    
    protected $validationResult;

    /**
     * Attempts to validate the form and returns the success state.
     * Automatically adds a UI message to tell the user that something
     * is missing in the form.
     *
     * @return boolean
     */
    public function validate()
    {
        if(isset($this->validationResult)) {
            return $this->validationResult;
        }

        $this->validationResult = $this->form->validate();

        if (!$this->validationResult && !$this->silentValidation) {
            $message = $this->form->getError();
            if (empty($message)) {
                $this->ui->addErrorMessage(
                    UI::icon()->warning() . ' ' .
                    '<b>' . t('Note:') . '</b> ' .
                    t('Some elements in the form are missing or not valid.') . ' ' .
                    t('Please review them below.')
                );
            } else {
                $this->ui->addErrorMessage($message);
            }
        }
        
        if($this->validationResult) {
            $this->postValidation();
        }

        return $this->validationResult;
    }
    
   /**
    * Retrieves all form element instances that have errors
    * after validation, as an indexed array with form element
    * instances.
    * 
    * @return HTML_QuickForm2_Node[]
    */
    public function getInvalidElements($simulateSubmit=false)
    {
        if($simulateSubmit) {
            $this->simulateSubmit();
        }
        
        if($this->validate()) {
            return array();
        }
        
        return $this->collectInvalidElements($this->form);
    }
    
    protected function collectInvalidElements(HTML_QuickForm2_Container $container, $collection=array())
    {
        /* @var $element HTML_QuickForm2_Node */
        $elements = $container->getElements();
        foreach($elements as $element) {
            if($element instanceof HTML_QuickForm2_Container) {
                $collection = $this->collectInvalidElements($element, $collection);
                continue;
            } 

            if(method_exists($element, 'hasErrors') && $element->hasErrors()) {
                $collection[] = $element;
            }
        }
        
        return $collection;
    }

   /**
    * Checks whether the form's submitted data is valid. If it
    * has not been validated yet, it is validated automatically.
    * 
    * @return boolean
    */
    public function isValid() : bool
    {
        return $this->validate();
    }
    
    protected int $dummyCounter = 0;

    private function generateDummyName() : string
    {
        $this->dummyCounter++;
        return 'form_el_' . $this->dummyCounter;
    }

   /**
    * Retrieves all form element values in an associative array.
    * 
    * @param boolean $removeTrackingVar
    * @return array<string,mixed>
    */
    public function getValues($removeTrackingVar=false)
    {
        /* @var $element HTML_QuickForm2_Node */

        $elements = $this->form->getElements();
        $values = array();
        foreach ($elements as $element) {
            $value = $element->getValue();
            $values[$element->getName()] = $value;
        }
        
        if($removeTrackingVar) {
            $varName = $this->getTrackingName();
            unset($values[$varName]);
        }

        return $values;
    }
    
   /**
    * @return HTML_QuickForm2_Element_ImageUploader[]
    */
    public function getImageUploaderElements()
    {
        $elements = $this->form->getElements();
        $result = array();
        foreach ($elements as $element) {
            if($element instanceof HTML_QuickForm2_Element_ImageUploader) {
                $result[] = $element;
            }
        }
        
        return $result;
    }
    
   /**
    * Retrieves the name of the request variable that is used by the
    * form to track whether it has been submitted.
    * 
    * @return string
    */
    public function getTrackingName()
    {
        return '_qf__'.$this->form->getId();
    }
    
    public function isTrackingElement(HTML_QuickForm2_Node $element)
    {
        if ($element->getName() == $this->getTrackingName()) {
            return true;
        }
        
        return false;
    }
    
    public function isDummyElement(HTML_QuickForm2_Node $element)
    {
        if(stristr($element->getName(), 'dummy')) {
            return true;
        }
        
        return false;
    }
    
    protected function _render()
    {
        return $this->renderHorizontal();
    }
    
    public function renderHorizontal()
    {
        return $this->renderLayout('horizontal');
    }
    
   /**
    * @var UI_Form_Renderer|NULL
    */
    protected ?UI_Form_Renderer $formRenderer = null;

    /**
     * Renders the form to HTML using the forms.elements form based on QuickForm's
     * array renderer.
     *
     * @return string
     */
    protected function renderLayout($layout)
    {
        if($this->readonly) {
            // do this again to ensure that all elements in the form get the info
            $this->makeReadonly();
        }
        
        $renderer = HTML_QuickForm2_Renderer::factory('array');
        
        $this->form->render($renderer);

        // the factory returns a renderer proxy, so instanceof does not work.
        if(is_callable(array($renderer, 'toArray')))
        {
            $renderer = new UI_Form_Renderer($this, $renderer->toArray(), $layout);
            $renderer->setRegistryEnabled($this->clientRegistry);
        }
        else
        {
            throw new Application_Exception(
                'Invalid form renderer',
                sprintf(
                    'Method [%s] missing from [%s].',
                    'toArray',
                    parseVariable($renderer)->enableType()->toString()
                ),
                self::ERROR_INVALID_FORM_RENDERER
            );
        }
        
        //$renderer->debugFormDef();
        
        $html = $renderer->render();
        
        $this->triggerEvent('rendered', array('renderer' => $renderer));
        $this->formRenderer = $renderer;

        return $html;
    }
    
    public function renderColumnized()
    {
        return $this->renderLayout('columnized');
    }

    protected $readonly = false;

   /**
    * Makes the form readonly so that it only shows element values,
    * without editing capabilities.
    * 
    * @return UI_Form
    */
    public function makeReadonly()
    {
        $this->readonly = true;
        $this->form->toggleFrozen(true);
        $this->form->addClass('frozen');
        return $this;
    }

    public function isReadonly()
    {
        return $this->readonly;
    }
    
    /**
     * Makes the field labels wider to allow for longer labels.
     * @return UI_Form
     */
    public function makeLabelsWider()
    {
        $this->form->addClass('wide-labels');
        return $this;
    }

   /**
    * Turns the form into a more compact form layout.
    * @return UI_Form
    */
    public function makeCondensed()
    {
        $this->form->addClass('form-condensed');
        return $this;
    }
    
   /**
    * Marks the form as being collapsible: all headers within the
    * form will be rendered so that their contained form elements
    * can be collapsed/expanded at will.
    * 
    * @return UI_Form
    */
    public function makeCollapsible()
    {
        $this->form->addClass('form-collapsible');
        return $this;
    }

   /**
    * Retrieves a format hint for any of the common 
    * regexes. The name is the name of the regex constant
    * minus the <code>REGEX_</code> (case insensitive), 
    * so for example:
    * 
    * getRegexHint('alias');
    * getRegexHint('name_or_title');
    * 
    * @param string $name
    * @return string
    */
    public static function getRegexHint($name)
    {
        $name = strtoupper($name);
        
        switch ($name) {
            case 'LABEL':
                return 
                    t('Allowed characters:') . ' ' . 
                    t('Regular words, digits and punctuation.');
                
            case 'ALIAS':
                return 
                    t('Allowed characters:') . ' ' .
                    t('Lowercase letters, digits, dots (.), underscores (_) and hyphens (-).') . ' ' .
                    t('Must start with a letter.');
                        
            case 'ALIAS_CAPITALS':
                return 
                    t('Allowed characters:') . ' ' .
                    t('Lowercase and uppercase letters, digits, dots (.), underscores (_) and hyphens (-).') . ' ' .
                    t('Must start with a letter.');
               
            case 'NAME_OR_TITLE':
                return t('May not contain any special characters or HTML.');

            case 'URL':
                return t('Must a a valid URL, if you omitted the http:// please add it.');
                
            case 'FILENAME':
                return
                t('Allowed characters:') . ' ' .
                t('Regular words, digits, spaces, dots (.), underscores (_), hyphens (-).') . ' ' . 
                t('Must start with a letter or number.');
                
            case 'EMAIL':
                return t('Must be a valid e-mail address.');
                
            case 'PHONE':
                return t('Allowed characters:') . ' ' .
                t('Digits, spaces, hyphens (-), plus sign (+).') . ' ' .
                t('Must start with a + or a digit.');
                
            case 'INTEGER':
                return t('Must be a whole number (without fractions).');
                
            case 'NOHTML':
                return t('May not contain HTML.');
                
            case 'FLOAT':
                return t('Must be a number with or without fractions.');
                
            case 'HEXCOLOR':
                return  t('Must be a hexadecimal color code with 3 or 6 characters.').' '.
                        t('Accepts lowercase and uppercase letters.');
        }
        
        throw new Application_Exception(
            'Unknown regex hint',
            sprintf(
                'The regex hint name [%s] was not recognized.',
                $name    
            ),
            self::ERROR_UNKNOWN_REGEX_HINT
        );
    }

    /**
     * Replaces commas with dots in a number, and removes spaces.
     * @param string $value
     * @return string
     */
    public function filter_adjustNumericNotation($value)
    {
        return str_replace(array(' ', ','), array('', '.'), $value);
    }

    /**
     * Retrieves the first element in the container's elements collection,
     * or null if it does not have any elements.
     *
     * @param HTML_QuickForm2_Container $container
     * @return NULL|HTML_QuickForm2_Node
     */
    public function getFirstElement(HTML_QuickForm2_Container $container)
    {
        return $this->walkElements($container);
    }

    /**
     * Walks through a QuickForm elements container and returns the first
     * regular form element it finds, or null if there are none.
     *
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Node|NULL
     */
    protected function walkElements(HTML_QuickForm2_Container $container)
    {
        $elements = $container->getElements();
        foreach ($elements as $element) {
            if($element->getType() == 'hidden') {
                continue;
            }
            
            $elementID = $element->getId();
            if (substr($elementID, 0, 5) == 'dummy') {
                continue;
            }
            
            if ($element instanceof HTML_QuickForm2_Container) {
                $result = $this->walkElements($element);
                if ($result) {
                    return $result;
                }

                continue;
            }

            return $element;
        }

        return null;
    }


    
    public function handle_validateMinMax($value, ?int $min, ?int $max) : bool
    {
        if(!is_numeric($value)) {
            return false;
        }

        $value = (int)$value;
        
        if($min !== null && $value < $min) {
            return false;
        }
        
        if($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }

   /**
    * Sets the onsubmit attribute of the form tag to the specified
    * javascript statement string.
    * 
    * @param string $statement
    * @return UI_Form
    */
    public function onSubmit($statement)
    {
        $this->form->setAttribute('onsubmit', $statement);
        return $this;
    }
    
    protected $elementIDs = array();
    
   /**
    * Creates an ID for a form element following the naming scheme standard
    * so that clientside scripts can access them easily as well.
    * 
    * @param string $jsid
    * @param string $elementName
    * @return string
    */
    public function createElementID($jsid, $elementName)
    {
        $elementID = $jsid.'_field_'.str_replace(array('[', ']'), array('_', ''), $elementName);
        
        if(isset($this->elementIDs[$elementID])) {
            throw new Application_Exception(
                'Duplicate form element ID',
                sprintf(
                    'The automatically generated id [%s] for form element [%s] already exists. '.
                    'When using array named elements, consider adding an index to ensure unique IDs can be generated within the form. '.
                    'For example: instead of naming elements text[], name them  text[0], text[1] etc...',
                    $elementID,
                    $elementName
                ),
                self::ERROR_DUPLICATE_ELEMENT_ID
            );
        }
        
        // we do it this way to avoid having to use in_array for performance reasons.
        $this->elementIDs[$elementID] = true;
        
        return $elementID;
    }
    
   /**
    * Validates the specified string with the regex for 
    * regular item labels.
    * 
    * @param string $label
    * @return boolean
    */
    public static function validateLabel($label)
    {
        if(preg_match(AppUtils\RegexHelper::REGEX_LABEL, $label)==1) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Validates the specified string with the regex for
    * item aliases.
    * 
    * @param string $alias
    * @return boolean
    */
    public static function validateAlias($alias)
    {
        if(preg_match(AppUtils\RegexHelper::REGEX_ALIAS, $alias)==1) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Validates the specified string with the regex for
    * email addresses.
    * 
    * @param string $email
    * @return boolean
    */
    public static function validateEmail($email)
    {
        if(preg_match(AppUtils\RegexHelper::REGEX_EMAIL, $email)) {
            return true;
        }
        
        return false;
    }
    
    protected $eventHandlers = array(
        'rendered' => array()
    );
    
   /**
    * Adds an event handler for the specified event type.
    * Throws an exception for unknown event types and uncallable
    * handlers.
    * 
    * When the event is triggered, the handler is called with two
    * parameters:
    * 
    * - The UI_Form instance
    * - An associative array with additional event-specific data
    * 
    * @param string $name
    * @param callable $handler
    * @throws Application_Exception
    * @return UI_Form
    */
    public function addEventHandler($name, $handler)
    {
        if(!isset($this->eventHandlers[$name])) {
            throw new Application_Exception(
                'Unknown event handler',
                sprintf(
                    'The form has no event [%s]. The available events are: [%s].',
                    $name,
                    implode(', ', array_keys($this->eventHandlers))    
                ),
                self::ERROR_UNKNOWN_EVENT_HANDLER  
            );
        }
        
        Application::requireCallableValid($handler, self::ERROR_INVALID_EVENT_HANDLER);
        
        $this->eventHandlers[$name][] = $handler;
        
        return $this;
    }
    
    protected function triggerEvent($name, $data=array())
    {
        foreach($this->eventHandlers[$name] as $handler) {
            call_user_func($handler, $this, $data);
        }
    }

    // region: Adding element flavors

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addText(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_InputText::class,
            $this->addElement('text', $name, $container)
        )
            ->setLabel($label);
    }

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_Textarea
     *
     * @throws Application_Formable_Exception
     * @throws BaseClassHelperException
     */
    public function addTextarea(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Textarea
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Textarea::class,
            $this->addElement('textarea', $name, $container)
        )
            ->setLabel($label);
    }

    /**
     * Adds a subheader to the form, which does not contain any data.
     * It is purely cosmetic and rendered using the form renderer.
     *
     * @param string $header
     * @param null|HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Node
     */
    public function addSubheader($header, $container = null)
    {
        return $this->addDummyElement($header, 'subheader', $container);
    }

    /**
     * Adds a collection of hidden variables to the form.
     * @param array $vars Name => value pairs
     * @return UI_Form
     */
    public function addHiddenVars($vars)
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }

        return $this;
    }

    /**
     * Adds an element to enter a percentage.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @param float $min
     * @param float $max
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addPercent(string $name, string $label, ?HTML_QuickForm2_Container $container=null, float $min=0, float $max=100) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addText($name, $label, $container);
        $el->addFilterTrim();
        $el->addClass('input-small');

        $this->addRulePercent($el, $min, $max);
        $this->setElementAppend($el, '%');

        return $el;
    }

    /**
     * Creates and adds an image uploader element (specific to SPIN
     * with the media classes).
     *
     * @param string $name
     * @param HTML_QuickForm2_Container|NULL $container The container to add the element to, defaults to the form itself.
     * @return HTML_QuickForm2_Element_ImageUploader
     * @throws Application_Exception
     */
    public function addImageUploader(string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_ImageUploader
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_ImageUploader::class,
            $this->addElement('imageuploader', $name, $container)
        );
    }

    /**
     * @param string $name
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_ExpandableSelect
     *
     * @throws Application_Formable_Exception
     * @throws ClassHelper\ClassNotExistsException
     * @throws ClassHelper\ClassNotImplementsException
     */
    public function addExpandableSelect(string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_ExpandableSelect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_ExpandableSelect::class,
            $this->addElement('ExpandableSelect', $name, $container)
        );
    }

    public function addVisualSelect(string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_VisualSelect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_VisualSelect::class,
            $this->addElement('visualselect', $name, $container)
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Node
     * @throws Application_Formable_Exception
     */
    private function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container) : HTML_QuickForm2_Node
    {
        try
        {
            return $this->resolveContainer($container)
                ->addElement($type, $name);
        }
        catch (HTML_QuickForm2_Exception $e)
        {
            throw new Application_Formable_Exception(
                'Cannot create form element',
                sprintf(
                    'Tried creating element of type [%s].',
                    $type
                ),
                self::ERROR_CANNOT_CREATE_ELEMENT,
                $e
            );
        }
    }

    /**
     * Adds a group to the form that will be rendered as
     * a tab. Note that if you use this, you should add
     * all form elements to tabs, and not add any single
     * elements to the form.
     *
     * @param string $name
     * @param string $label
     * @param string|NULL $description
     * @return HTML_QuickForm2_Container_Group
     */
    public function addTab(string $name, string $label, ?string $description = null) : HTML_QuickForm2_Container_Group
    {
        $tab = $this->form->addGroup($name);
        $tab->setAttribute('rel', 'tab');
        $tab->setLabel($label);
        $tab->setId(self::ID_PREFIX . $name);
        $tab->setAttribute('description', $description);

        return $tab;
    }

    /**
     * Adds a purely cosmetic header to the form that has no data.
     *
     * @param string $title
     * @param HTML_QuickForm2_Container|NULL $container
     * @param string|null $anchor The name of an anchor to jump to this header in the page
     * @param bool $collapsed
     * @return HTML_QuickForm2_Element_InputText
     * @deprecated
     */
    public function addHeader(string $title, ?HTML_QuickForm2_Container $container = null, ?string $anchor=null, bool $collapsed=true) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addDummyElement($title, 'header', $container);

        if(!empty($anchor)) {
            $el->setAttribute('data-anchor', $anchor);
        }

        if(!$collapsed) {
            $el->setAttribute('data-collapsed', 'no');
        }

        return $el;
    }

    public function addStatic(string $label, string $content, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        if(empty($container)) {
            $container = $this->form;
        }

        $element = $this->addDummyElement($label, 'static', $container);
        $element->setAttribute('static_content', $content);

        return $element;
    }

    /**
     * Adds arbitrary HTML code to the form.
     * @param string|int|float|bool|Interface_Stringable|NULL $html
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addHTML($html, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        return $this->addDummyElement((string)$html, 'html', $container);
    }

    /**
     * Adds a purely cosmetic hint message to the form, styled as an
     * informational message that has no data.
     *
     * @param string $text
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Node
     */
    public function addHint($text, $container = null)
    {
        return $this->addDummyElement($text, 'hint', $container);
    }

    /**
     * Adds an ISO 8601 date element (YYYY-MM-DD).
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addISODate(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->form->addText($name, $container);
        $el->addFilterTrim();
        $el->setLabel($label);
        $el->addClass('input-xlarge');

        $this->addRuleISODate($el);

        return $el;
    }

    public function addInteger(string $name, string $label, ?HTML_QuickForm2_Container $container=null, int $min=0, int $max=0) : HTML_QuickForm2_Element_InputText
    {
        $element = $this->addText($name, $label, $container);
        $element->addFilterTrim();
        $element->addClass('input-small');

        $this->addRuleInteger($element, $min, $max);

        return $element;
    }

    /**
     * Adds a purely cosmetic text paragraph to the form. Has no data.
     * @param string $text
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Node
     */
    public function addParagraph($text, $container = null)
    {
        return $this->addDummyElement($text, 'paragraph', $container);
    }

    /**
     * Adds a dummy element of the specified type with the specified content.
     * If no container is set, the form itself is used.
     *
     * @param string $content
     * @param string $type
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_InputText
     */
    protected function addDummyElement(string $content, string $type, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        if(is_null($container))
        {
            $container = $this->form;
        }

        $this->dummyCounter++;

        $element = $container->addElement('text', 'dummy' . $this->dummyCounter);
        $element->setLabel($content);
        $element->setAttribute('rel', $type);

        return ensureType(
            HTML_QuickForm2_Element_InputText::class,
            $element
        );
    }

    /**
     * Adds a hidden variable to the form that will get submitted along with visible fields.
     *
     * @param string $name
     * @param string|null $value
     * @param string|null $id
     * @return HTML_QuickForm2_Element_InputHidden
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function addHiddenVar(string $name, ?string $value = null, ?string $id = null) : HTML_QuickForm2_Element_InputHidden
    {
        if (!isset($this->hiddens[$name]))
        {
            $this->hiddens[$name] = $this->form->addHidden($name);
        }

        if($value === null) {
            $value = (string)$this->hiddens[$name]->getValue();
        }

        if(!empty($value)) {
            $this->hiddens[$name]->setAttribute('value', $value);
        }

        if ($id === null)
        {
            $id = self::ID_PREFIX . $name;
        }

        $this->hiddens[$name]->setId($id);

        return $this->hiddens[$name];
    }

    /**
     * Adds a clickable button to the form that links to the specified URL.
     * @param string $url
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws UI_Exception
     */
    public function addLinkButton(string $url, $label, $tooltip='') : HTML_QuickForm2_Element_UIButton
    {
        return $this->addButton($this->generateDummyName())
            ->setTooltip($tooltip)
            ->link($url)
            ->setLabel($label);
    }

    /**
     * Adds the primary submit button to the form's footer.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws UI_Exception
     */
    public function addPrimarySubmit($label, string $name='save', $tooltip='') : HTML_QuickForm2_Element_UIButton
    {
        return $this->addSubmit($label, $name, $tooltip)
            ->makePrimary();
    }

    /**
     * Adds a primary submit button to the form's footer, which
     * automatically enables simulation mode before submitting
     * the form.
     *
     * @param string $label
     * @param string $name
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws UI_Exception
     */
    public function addDevPrimarySubmit(string $label, string $name='save') : HTML_QuickForm2_Element_UIButton
    {
        return $this->addSubmit($label, $name)
            ->makeDeveloper()
            ->click("FormHelper.enableSimulation($(this).closest('form').attr('id'))");
    }

    /**
     * Adds a blank button to the footer of the form,
     * which must be configured further with a label
     * and action.
     *
     * @param string $name
     * @return HTML_QuickForm2_Element_UIButton
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addButton(string $name) : HTML_QuickForm2_Element_UIButton
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_UIButton::class,
            $this->addElement('uibutton', $name, $this->form)
        );
    }

    /**
     * Adds an unstyled submit button to the form's footer.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws UI_Exception
     */
    public function addSubmit($label, string $name='save', $tooltip=null) : HTML_QuickForm2_Element_UIButton
    {
        return $this->addButton($name)
            ->makeSubmit()
            ->setLabel(toString($label))
            ->setTooltip($tooltip);
    }

   /**
    * Creates a bootstrap switch element.
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container Optional container to add the element to, defaults to the form itself.
    * @return HTML_QuickForm2_Element_Switch
    */
    public function addSwitch(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Switch
    {
        if(!$container) {
            $container = $this->form;
        }
        
        $element = $container->addElement('switch', $name);
        $element->setLabel($label);
        
        return ensureType(
            HTML_QuickForm2_Element_Switch::class,
            $element
        );
    }
    
   /**
    * Adds a select element. 
    * 
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_Select
    */
    public function addSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        if(!$container) {
            $container = $this->form;
        }
        
        $el = $container->addElement('select', $name);
        $el->setLabel($label);
        
        return ensureType(
            HTML_QuickForm2_Element_Select::class,
            $el
        );
    }

    /**
     * Adds a bootstrap multiselect element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Multiselect
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addMultiselect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Multiselect
    {
        $el = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Multiselect::class,
            $this->addElement('multiselect', $name, $container)
        );

        $el->setLabel($label);

        return $el;
    }

    /**
     * Creates and adds a generic alias form element, complete
     * with validation rule and validation hints.
     *
     * @param string|null $name Defaults to [alias].
     * @param string|null $label Defaults to [Alias].
     * @param string|null $comment Additional text to prepend before the validation hints.
     * @param boolean $structural Whether this alias is to be marked as a structural field.
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addAlias(?string $name=null, ?string $label=null, ?string $comment=null, bool $structural=true) : HTML_QuickForm2_Element_InputText
    {
        if(empty($name))
        {
            $name = 'alias';
        }
        
        if(empty($label))
        {
            $label = t('Alias');
        }
        
        $el = $this->addText($name, $label);
        $el->addFilterTrim();
        $this->makeRequired($el);
        $el->setComment($comment);
        $el->setAttribute('data-type', 'alias');

        $this->addRuleRegex(
            $el,
            RegexHelper::REGEX_ALIAS,
            t('Invalid format, please review the format hints.')
        );

        if($structural)
        {
            $this->makeStructural($el);
        }
        
        return $el;
    }

    /**
     * Creates a datepicker element and adds it to the form, or the
     * specified container.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_HTMLDateTimePicker
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addDatepicker(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $this->registerCustomElement(self::ELEMENT_TYPE_DATE_PICKER, ClassHelper::getClassTypeName(HTML_QuickForm2_Element_HTMLDateTimePicker::class));
        
        $element = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_HTMLDateTimePicker::class,
            $this->addElement(self::ELEMENT_TYPE_DATE_PICKER, $name, $container)
        );
        
        $element->setLabel($label);

        return $element;
    }

    // endregion

    /**
    * Adds an email validation rule to the element.
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleEmail(HTML_QuickForm2_Element $element)
    {
        $element->addRule('regex', t('Must be a valid e-mail address.'), AppUtils\RegexHelper::REGEX_EMAIL);
        $element->setAttribute('data-type', 'email');
        return $element;
    }
    
   /**
    * Adds a phone number validation rule to the element.
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRulePhone(HTML_QuickForm2_Element $element)
    {
        $element->addRule('regex', t('Must be a valid phone number.'), AppUtils\RegexHelper::REGEX_PHONE);
        $element->setAttribute('data-type', 'phone');
        return $element;
    }

    /**
     * Adds an alias validation rule to the element.
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleAlias(HTML_QuickForm2_Element $element, $allowCapitalLetters=false)
    {
        $regex = AppUtils\RegexHelper::REGEX_ALIAS;
        $dataType = 'alias';
        
        if($allowCapitalLetters) 
        {
            $regex = AppUtils\RegexHelper::REGEX_ALIAS_CAPITALS;
            $dataType = 'alias_capitals';
        }
        
        $this->makeLengthLimited($element, 1, 80);
        
        $element->addRule('regex', t('Must be a valid alias.'), $regex);
        $element->setAttribute('data-type', $dataType);
        return $element;
    }

    /**
     * Adds a callback rule to the element. The first argument
     * is always the value to validate, and the last is the
     * rule object instance, even if custom arguments are specified.
     *
     * @param HTML_QuickForm2_Element $element
     * @param callable $callback
     * @param string $errorMessage
     * @param array $arguments Arguments for the callback, as indexed array of parameters.
     * @return HTML_QuickForm2_Rule_Callback
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws HTML_QuickForm2_InvalidArgumentException
     * @throws HTML_QuickForm2_NotFoundException
     */
    public function addRuleCallback(HTML_QuickForm2_Element $element, $callback, string $errorMessage, array $arguments=array()) : HTML_QuickForm2_Rule_Callback
    {
        $rule = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Rule_Callback::class,
            $element->addRule(
                'callback',
                $errorMessage,
                'trim'
            )
        );

        if(!is_array($arguments)) {
            // this is to ensure the arguments are always passed on
            // and in the correct order, even if the arguments should
            // be null, since this may be intentional (There was an
            // empty check here before, which caused issues).
            $arguments = array($arguments);
        }
        
        $arguments[] = $rule;
        
        $rule->setConfig(array(
            'callback' => $callback,
            'arguments' => $arguments
        ));

        return $rule;
    }
    
   /**
    * Adds a filename validation rule, which checks that the
    * name has an extension and contains only valid characters.
    *  
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleFilename(HTML_QuickForm2_Element $element)
    {
        $element->addRule('regex', t('Please enter a valid name.'), RegexHelper::REGEX_FILENAME);
        
        $element->setAttribute('data-type', 'filename');
        
        return $element;
    }
    
   /**
    * Adds an integer validation rule to the element.
    * @param HTML_QuickForm2_Element $element
    * @param int $min
    * @param int $max
    * @return HTML_QuickForm2_Element
    */
    public function addRuleInteger(HTML_QuickForm2_Element $element, int $min=0, int $max=0)
    {
        new UI_Form_Validator_Integer($this, $element, $min, $max);
        
        return $element;
    }
    
    /**
     * Adds a datetime validation rule to the element.
     * 
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     * @see UI_Form::validateISODate()
     */
    public function addRuleISODate(HTML_QuickForm2_Element $element)
    {
        new UI_Form_Validator_ISODate($this, $element);

        return $element;
    }
    
    public function validateISODate($date, HTML_QuickForm2_Rule_Callback $rule) : bool
    {
        $date = strval($date);
        
        if(empty($date))
        {
            return true;
        }
        

        return true;
    }

   /**
    * Adds a float validation rule to the element.
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleFloat(HTML_QuickForm2_Element $element, float $min=0, float $max=0)
    {
        $element->addFilter(array($this, 'callback_convertComma'));
        
        new UI_Form_Validator_Float($this, $element, $min, $max);
        
        return $element;
    }

    public function addRulePercent(HTML_QuickForm2_Element $element, float $min=0, float $max=100) : HTML_QuickForm2_Element
    {
        new UI_Form_Validator_Percent($this, $element, $min, $max);

        return $element;
    }

   /**
    * Converts commas in the value to dots (used for numeric values)
    * @param mixed $value
    * @return string
    */
    public function callback_convertComma($value) : string
    {
        $value = strval($value);

        if(!empty($value))
        {
            $value = str_replace(array(' ', ','), '.', $value);
        }
        
        return $value;
    }
    
   /**
    * Adds a rule for a date format. This is always in the english format
    * with the month on front, e.g. [11/02/2015]. Can optionally contain
    * a time at the end, in the form [11/02/2015 08:42].
    * 
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleDate(HTML_QuickForm2_Element $element)
    {
        new UI_Form_Validator_Date($this, $element);
        
        return $element;
    }
    
   /**
    * Makes the page initially scroll to the header identified
    * by its anchor name.
    * 
    * Example usage:
    * 
    * $wrapper->addHeader('Title', null, 'AnchorName');
    * $wrapper->scrollToHeader('AnchorName');
    * 
    * @param string $anchorName
    */
    public function scrollToHeader($anchorName)
    {
        $this->ui->addJavascriptOnload("setTimeout(function() {UI.ScrollToElement('#" . $anchorName . "');}, 1000)");
    }
    
   /**
    * Adds a redactor to the target element, and returns the
    * redactor helper instance for additional configuration.
    * 
    * @param HTML_QuickForm2_Element $element
    * @return UI_MarkupEditor_Redactor
    */
    public function makeRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_Redactor
    {
        return ensureType(
            UI_MarkupEditor_Redactor::class,
            $this->makeMarkupEditor('Redactor', $element, $country)
        );
    }
    
    public function makeCKEditor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_CKEditor
    {
        return ensureType(
            UI_MarkupEditor_CKEditor::class,
            $this->makeMarkupEditor('CKEditor', $element, $country)
        );
    }
    
    public function makeMarkupEditor(string $editorID, HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor
    {
        $editor = $this->ui->addMarkupEditor($editorID, $element, $country);
        
        $element->setAttribute('data-type', 'markup-editor');
        $element->setAttribute('data-editor-id', $editorID);
        
        $element->setRuntimeProperty('markup-editor', $editor);
        
        return $editor;
    }
    
   /**
    * Post validation routine: gives all elements time to post-process
    * a valid form. This is used for example for the image elements so
    * uploaded images can be automatically transformed into regular media
    * documents once the form is valid and will be processed.
    * 
    * @see HTML_QuickForm2_Element_ImageUploader::upgradeMedia()
    */
    protected function postValidation()
    {
        $imageEls = $this->getImageUploaderElements();
        foreach($imageEls as $imageEl) {
            $imageEl->upgradeMedia();
        }
    }

    // region: Modifying elements

    /**
     * Adds a callback function that will be called when the element is
     * rendered, to be able to influence how the element is rendered.
     *
     * The callback function gets an instance of the element's renderer
     * class, which provides an API to customize the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @param callable $callback
     * @throws Application_Exception
     *
     * @see UI_Form_Renderer_Element
     */
    public function addRenderCallback(HTML_QuickForm2_Node $element, $callback) : void
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_RENDER_CALLBACK);

        $collection = $element->getRuntimeProperty('render-callbacks');
        if(empty($collection)) {
            $collection = array();
        }

        $collection[] = $callback;

        $element->setRuntimeProperty('render-callbacks', $collection);
    }

    /**
     * Marks the element as structural, meaning changing
     * it will trigger a new revision when saving.
     *
     * @param HTML_QuickForm2_Node $el
     * @param boolean $structural Whether it is structural or not
     * @return HTML_QuickForm2_Node
     */
    public function makeStructural(HTML_QuickForm2_Node $el, bool $structural=true)
    {
        $el->setAttribute('structural', AppUtils\ConvertHelper::bool2string($structural, true));
        return $el;
    }

    /**
     * Sets the element to be rendered as a standalone element:
     * this hides the label, and removes the element's indentation
     * so it can use the full available width.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function makeStandalone(HTML_QuickForm2_Element $element)
    {
        $element->setAttribute('standalone', 'yes');
        return $element;
    }

    /**
     * Adds a validation rule to make the element required.
     *
     * @param HTML_QuickForm2_Node $el
     * @return HTML_QuickForm2_Node
     */
    public function makeRequired(HTML_QuickForm2_Node $el, $message=null)
    {
        if(empty($message))
        {
            if($el instanceof HTML_QuickForm2_Element_Select) {
                $message = t('Please select a value.');
            } else {
                $message = t('Please enter a value.');
            }
        }

        $el->addRule('required', $message);

        $el->setAttribute('data-required', 'true');
        return $el;
    }

    /**
     * Adds a validation rule to the element to limit the length
     * to the specified amount of characters. Automatically adds
     * a validation hint for the length as well.
     *
     * Note: To limit to a specific length, simply set the min and max
     * to the same value.
     *
     * @param HTML_QuickForm2_Node $el
     * @param int $min
     * @param int $max
     * @return HTML_QuickForm2_Node
     */
    public function makeLengthLimited(HTML_QuickForm2_Node $el, $min, $max)
    {
        if($min > $max) {
            throw new Application_Exception(
                'Invalid length',
                sprintf(
                    'The minimum length [%s] is higher than the maximum [%s].',
                    $min,
                    $max
                ),
                self::ERROR_INVALID_LENGTH_LIMIT
            );
        }

        if($min==$max) {
            $el->addRule(
                'length',
                t('Has to be exactly %1$s characters long.', $min),
                array($min, $max)
            );
        } else {
            $el->addRule(
                'length',
                t('Has to be between %1$s and %2$s characters long.', $min, $max),
                array($min, $max)
            );
        }

        $el->setAttribute('data-length', $min . '-' . $max);

        return $el;
    }

    /**
     * @param HTML_QuickForm2_Node $el
     * @param int|null $min
     * @param int|null $max
     * @return HTML_QuickForm2_Node
     * @throws Application_Exception
     * @throws HTML_QuickForm2_InvalidArgumentException
     * @throws HTML_QuickForm2_NotFoundException
     */
    public function makeMinMax(HTML_QuickForm2_Node $el, ?int $min=null, ?int $max=null) : HTML_QuickForm2_Node
    {
        $minEmpty = $min === null || $min <= 0;
        $maxEmpty = $max === null || $max <= 0;

        if($minEmpty && $maxEmpty)
        {
            return $el;
        }

        if(!$minEmpty && !$maxEmpty && $min > $max)
        {
            throw new Application_Exception(
                'Invalid length',
                sprintf(
                    'The minimum value [%s] is higher than the maximum [%s] for element [%s].',
                    $min,
                    $max,
                    $el->getName()
                ),
                self::ERROR_INVALID_MINMAX_VALUES
            );
        }

        $el->setAttribute('data-validate', 'minmax');
        $el->setAttribute('data-min', (string)$min);
        $el->setAttribute('data-max', (string)$max);

        $el->addRule(
            'callback',
            '',
            array(
                'callback' => array($this, 'handle_validateMinMax'),
                'arguments' => array($min, $max)
            )
        );

        return $el;
    }

    /**
     * Hides this element from the frozen variant of the form.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function makeHiddenWhenReadonly(HTML_QuickForm2_Element $element)
    {
        $element->setAttribute('data-hidden-when-frozen', 'yes');
        return $element;
    }

    /**
     * Appends the related units for the element's values to the
     * element in the UI, for example "Centimetres".
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $units
     * @return HTML_QuickForm2_Element
     */
    public function setElementUnits(HTML_QuickForm2_Element $element, $units)
    {
        return $this->setElementAppend($element, $units);
    }

    public function addFilterComma2Dot(HTML_QuickForm2_Element $element) : HTML_QuickForm2_Element
    {
        $element->addFilter(array($this, 'callback_convertComma'));

        return $element;
    }

   /**
    * Adds a string to append to an element. For example
    * for units, like "Centimetres".
    * 
    * @param HTML_QuickForm2_Element $element
    * @param string $appendString
    * @return HTML_QuickForm2_Element
    */
    public function setElementAppend(HTML_QuickForm2_Element $element, $appendString)
    {
        $element->setAttribute('data-append', $appendString);
        return $element;
    }
    
   /**
    * Adds a string to prepend to an element. For example
    * for units, like "Centimetres".
    * 
    * @param HTML_QuickForm2_Element $element
    * @param string $prependString
    * @return HTML_QuickForm2_Element
    */
    public function setElementPrepend(HTML_QuickForm2_Element $element, $prependString)
    {
        $element->setAttribute('data-prepend', $prependString);
        return $element;
    }

    protected function addElementHTML(string $position, HTML_QuickForm2_Element $element, $html, bool $whenFrozen=false) : HTML_QuickForm2_Element
    {
        $collection = $element->getRuntimeProperty($position.'-html', array());

        $collection[] = array(
            'html' => $html,
            'whenFrozen' => $whenFrozen
        );

        $element->setRuntimeProperty($position.'-html', $collection);

        return $element;
    }

   /**
    * Adds custom HTML after the element's form input element.
    * Not to mistake with {@link setElementAppend()}.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param string $html
    * @param boolean $whenFrozen Whether to display this even when the element is frozen.
    * @return HTML_QuickForm2_Element
    * @see prependElementHTML()
    * @see setElementAppend()
    */
    public function appendElementHTML(HTML_QuickForm2_Element $element, $html, $whenFrozen=false)
    {
        return $this->addElementHTML('append', $element, $html, $whenFrozen);
    }
    
   /**
    * Appends a button after the element's input.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param UI_Button|UI_Bootstrap $button
    * @param boolean $whenFrozen Whether to display this even when the element is frozen.
    * @return HTML_QuickForm2_Element
    */
    public function appendElementButton(HTML_QuickForm2_Element $element, $button, $whenFrozen=false)
    {
        $button->addClass('after-input');
        return $this->appendElementHTML($element, $button, $whenFrozen);
    }
    
    /**
     * Appends a button to the element to generate an alias from the content
     * of the target element. Uses the AJAX transliterate function to create
     * the alias from a string.
     *
     * @param HTML_QuickForm2_Element $aliasElement
     * @param HTML_QuickForm2_Element $fromElement
     * @return HTML_QuickForm2_Element
     */
    public function appendGenerateAliasButton(HTML_QuickForm2_Element $aliasElement, HTML_QuickForm2_Element $fromElement)
    {
        return $this->appendElementButton(
            $aliasElement,
            UI::button(t('Generate from %1$s', $fromElement->getLabel()))
            ->setIcon(UI::icon()->generate())
            ->click(
                sprintf('FormHelper.generateAlias(this, %s ,%s)',
                    JSHelper::phpVariable2JS($aliasElement->getId(), JSHelper::QUOTE_STYLE_SINGLE),
                    JSHelper::phpVariable2JS($fromElement->getId(), JSHelper::QUOTE_STYLE_SINGLE)
                )
            )
        );
    }

   /**
    * Adds custom HTML before the element's form input element.
    * Not to mistake with {@link setElementPrepend()}.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param string $html
    * @return HTML_QuickForm2_Element
    * @see appendElementHTML()
    * @see setElementPrepend()
    */
    public function prependElementHTML(HTML_QuickForm2_Element $element, $html, $whenFrozen=false)
    {
        return $this->addElementHTML('prepend', $element, $html, $whenFrozen);
    }

    // endregion

    /**
     * Adds a label validation rule to the element.
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleLabel(HTML_QuickForm2_Element $element)
    {
        $element->addRule('regex', t('Must be a valid label.'), AppUtils\RegexHelper::REGEX_LABEL);
        $element->setAttribute('data-type', 'label');
        return $element;
    }

    /**
     * Adds a name or title validation rule to the element.
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleNameOrTitle(HTML_QuickForm2_Element $element)
    {
        $element->addRule('regex', t('Must be a valid title.'), AppUtils\RegexHelper::REGEX_NAME_OR_TITLE);
        $element->setAttribute('data-type', 'name_or_title');
        return $element;
    }
    
   /**
    * Adds a rule that disallows using HTML in the element.
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleNoHTML(HTML_QuickForm2_Element $element)
    {
        $element->addRule('notregex', t('May not contain HTML.'), AppUtils\RegexHelper::REGEX_IS_HTML);
        $element->setAttribute('data-type', 'nohtml');
        return $element;
    }

   /**
    * Adds a rule to confirm the input to the specified regex.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param string $regex
    * @param string $message
    * @return HTML_QuickForm2_Element
    */
    public function addRuleRegex(HTML_QuickForm2_Element $element, string $regex, string $message) : HTML_QuickForm2_Element
    {
        $element->addRule('regex', $message, $regex);
        
        return $element;
    }
    
   /**
    * Parses a date string into a date object. The date must have
    * a format matching the  {@link REGEX_DATE} regular expression.
    * 
    * @param string $dateString
    * @return NULL|DateTime
    */
    public static function parseDate($dateString)
    {
        $matches = null;
        preg_match_all('%([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4}) ([0-9]{1,2}):([0-9]{1,2})|([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})%i', $dateString, $matches, PREG_PATTERN_ORDER);
        
        if(!isset($matches[0]) || empty($matches[0])) {
            return null;
        }
        
        try{
            $date = new DateTime($matches[0][0]);
        } catch(Exception $e) {
            return null;
        }
        
        return $date;
    }
    
    protected $title;
    
   /**
    * Sets the title of the form. This is typically used 
    * in the form rendering template as title for the content
    * section in which the form is shown.
    * 
    * @param string $title
    * @return UI_Form
    */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    protected $abstract;
    
   /**
    * Sets the abstract of the form. This is typically used 
    * in the form rendering template as title for the content
    * section in which the form is shown.
    * 
    * @param string $abstract
    * @return UI_Form
    */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
        return $this;
    }
    
    public function getAbstract()
    {
        return $this->abstract;
    }
    
   /**
    * Retrieves the matching UI_Form instance for the specified
    * QuickForm element, or NULL if it could not be found.
    * 
    * @param HTML_QuickForm2_Node $el
    * @return UI_Form|NULL
    */
    public static function getInstanceByElement(HTML_QuickForm2_Node $el)
    {
        $id = $el->getId();
        if(empty($id)) {
            throw new Application_Exception(
                'Element ID missing',
                sprintf(
                    'The element [%s] has no ID, this is mandatory for finding its form instance.',
                    $el->getName()
                ),
                self::ERROR_ELEMENT_HAS_NO_ID
            );
        }
        
        foreach(self::$instances as $instance) {
            $element = $instance->getForm()->getElementById($id);
        
            if($element) {
                return $instance;
            }
        }
        
        return null;
    }
    
    public function hasElements($includeHiddens=false)
    {
        $elements = $this->getForm()->getElements();
        $total = count($elements);
        for($i=0; $i<$total; $i++) {
            $type = $elements[$i]->getType();
            if(!$includeHiddens && $type == 'hidden') {
                continue;
            }
            
            return true;
        }
        
        return false;
    }
    
    public function getName()
    {
        $name = $this->form->getName();
        if(empty($name)) {
            $name = $this->form->getId();
        }
        
        return str_replace(self::FORM_PREFIX, '', $name);
    }
    
   /**
    * Gets the javascript statement that can be used to submit
    * the form, optionally in simulation mode.
    * 
    * @param bool $simulate
    * @return string
    */
    public function getJSSubmitHandler($simulate=false)
    {
        return self::renderJSSubmitHandler($this, $simulate);
    }
    
    public static function resolveFormName($subject) : string
    {
        if($subject instanceof UI_DataGrid)
        {
            return $subject->getFormID();
        }
        
        if($subject instanceof UI_Form)
        {
            return $subject->getName();
        }
        
        if($subject instanceof Application_Formable)
        {
            return $subject->getFormableName();
        }
        
        if(is_string($subject))
        {
            return $subject;
        }
        
        return '';
    }
    
   /**
    * Renders a JavaScript statement that can be used to 
    * submit the target form.
    * 
    * @param string|UI_Form|Application_Formable|UI_DataGrid $subject
    * @param boolean $simulate
    * @return string
    */
    public static function renderJSSubmitHandler($subject, bool $simulate=false) : string
    {
        $formName = self::resolveFormName($subject);

        if(!empty($formName))
        {
            return sprintf(
                "application.submitForm('%s', %s)",
                $formName,
                AppUtils\ConvertHelper::bool2string($simulate)
            );
        }
        
        throw new Application_Exception(
            'Unhandled submit subject',
            sprintf(
                'The subject of type [%s] is not compatible with the available form types.',
                gettype($subject)
            ),
            self::ERROR_UNHANDLED_SUBMIT_HANDLER_SUBJECT
        );
    
    }
  
   /**
    * @var boolean
    */
    protected $clientRegistry = false;
    
   /**
    * Enables or disables the clientside form elements registry: this
    * is an easy way to access information on the form on the client
    * side, from sections to individual elements. By default this is 
    * disabled.
    * 
    * @param bool $enabled
    * @return UI_Form
    */
    public function enableClientRegistry(bool $enabled=true)
    {
        if(isset($this->formRenderer)) 
        {
            $this->formRenderer->setRegistryEnabled($enabled);
        }
        
        $this->clientRegistry = $enabled;
        
        return $this;
    }

    /**
     * When there are no submit buttons directly in a form,
     * we can make it possible to submit it via the enter key by
     * adding this invisible submit button. The div does not
     * use visibility or display to hide it, since Google
     * Chrome will not accept the enter key for a hidden
     * submit element.
     *
     * @param string $id Custom ID for the element, if needed
     * @return string
     */
    public static function renderDummySubmit(string $id='') : string
    {
        if(empty($id)) {
            $id = nextJSID();
        }
        
        return
        '<div style="width:1px;height:1px;overflow:hidden;">'.
            '<input id="'.$id.'" type="submit" name="dummySubmit_'.$id.'" value="true" tabindex="-1"/>'.
        '</div>';
    }

    public function compileExamples()
    {
        return
        t('Examples:').' '.
        '<span class="form-example-string">'.
            implode('</span>, <span class="form-example-string">', func_get_args()).
        '</span>';
    }

    public function compileValues()
    {
        return
        t('Possible values:').' '.
        '<span class="form-example-string">'.
            implode('</span>, <span class="form-example-string">', func_get_args()).
        '</span>';
    }

    protected function resolveContainer(?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Container
    {
        if($container)
        {
            return $container;
        }
        
        return $this->form;
    }

    public function getElementValidator(HTML_QuickForm2_Element $element) : ?UI_Form_Validator
    {
        $validator = $element->getRuntimeProperty('validator');

        if($validator instanceof UI_Form_Validator)
        {
            return $validator;
        }

        return null;
    }
}
