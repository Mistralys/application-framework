<?php
/**
 * @package Application
 * @subpackage Forms
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Application;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\ConvertHelper_Exception;
use AppUtils\FileHelper;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\JSHelper;
use AppUtils\RegexHelper;
use HTML\QuickForm2\DataSource\ManualSubmitDataSource;
use UI\AdminURLs\AdminURLInterface;
use UI\Form\FormException;

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
    public const int ERROR_DUPLICATE_ELEMENT_ID = 45524001;
    public const int ERROR_INVALID_FORM_DATA = 45524002;
    public const int ERROR_UNKNOWN_REGEX_HINT = 45524003;
    public const int ERROR_UNKNOWN_EVENT_HANDLER = 45524004;
    public const int ERROR_INVALID_EVENT_HANDLER = 45524005;
    public const int ERROR_INVALID_LENGTH_LIMIT = 45524006;
    public const int ERROR_ELEMENT_HAS_NO_ID = 45524007;
    public const int ERROR_MINMAX_VALUES_EMPTY = 45524008;
    public const int ERROR_INVALID_MINMAX_VALUES = 45524009;
    public const int ERROR_MINMAX_VALUES_NOT_A_NUMBER = 45524010;
    public const int ERROR_OBSOLETE_IMAGE_ELEMENT = 45524011;
    public const int ERROR_INVALID_RENDER_CALLBACK = 45524012;
    public const int ERROR_UNHANDLED_SUBMIT_HANDLER_SUBJECT = 45524013;
    public const int ERROR_INVALID_FORM_RENDERER = 45524014;
    public const int ERROR_INVALID_DATEPICKER_ELEMENT = 45524015;
    public const int ERROR_CANNOT_CREATE_ELEMENT = 45524016;
    public const int ERROR_COULD_NOT_SUBMIT_FORM = 45524017;
    public const int ERROR_ELEMENT_NOT_FOUND = 45524018;

    /**
     * Stores the string that form element IDs get prefixed with.
     * @var string
     */
    public const string ID_PREFIX = 'f-';

    public const string ATTRIBUTE_LABEL_ID = 'data-label-id';
    public const string REL_BUTTON = 'Button';
    public const string REL_LAYOUT_LESS_GROUP = 'LayoutlessGroup';
    public const string FORM_PREFIX = 'form-';
    public const string ELEMENT_TYPE_DATE_PICKER = 'datepicker';


    protected string $id;
    protected HTML_QuickForm2 $form;
    protected HTML_QuickForm2_DataSource_Array $defaultDataSource;

    /**
     * Creates a new form. Use the {@link getForm()} method to configure
     * the QuickForm object.
     *
     * @param UI $ui
     * @param string $formID
     * @param string $method
     * @param array<string,mixed>|ArrayDataCollection $defaultData
     * @throws FormException
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function __construct(UI $ui, string $formID, string $method, $defaultData = array())
    {
        parent::__construct($ui->getPage());

        $this->registerCustomElements();
        $this->registerCustomRules();

        if($defaultData instanceof ArrayDataCollection) {
            $defaultData = $defaultData->getData();
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

    /**
     * Sets the ID to use for the form element's <code>&lt;label&gt;</code>
     * tag. This is used by the {@see UI_Form_Renderer} to adjust the label's
     * target (instead of using the element's own ID).
     *
     * Use cases are when an element has sub-elements (like groups), to be
     * able to specify what the target should be.
     *
     * @param HTML_QuickForm2_Node $node
     * @param string $id
     * @return void
     */
    public static function setElementLabelID(HTML_QuickForm2_Node $node, string $id) : void
    {
        $node->setAttribute(self::ATTRIBUTE_LABEL_ID, $id);
    }

    public function getJSID() : string
    {
        return $this->form->getAttribute('data-jsid');
    }

    public function callback_onNodeAdded(HTML_QuickForm2_Event_NodeAdded $event) : void
    {
        $node = $event->getNode();

        // Adds the filter to strip control characters from all text elements.
        if($node instanceof HTML_QuickForm2_Element_Textarea || $node instanceof HTML_QuickForm2_Element_InputText)
        {
            $node->addFilter(array(ConvertHelper::class, 'stripControlCharacters'));
            $node->setAttribute('data-strip-controlchars', 'yes');
        }
    }

    protected static bool $customElementsRegistered = false;

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

        foreach($this->getClassFolders() as $folder) {
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

    private function getClassFolders() : array
    {
        $driver = Application_Driver::getInstance();
        $app = $driver->getApplication();

        // Start with the built-in form elements
        $folders = array($app->getClassesFolder().'/UI/Form/Element');

        // Then add application-specific and external form elements
        array_push($folders, ...AppFactory::createFoldersManager()->choose()->formElements()->resolveFolders());

        return $folders;
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
     * @throws BaseClassHelperException
     */
    public function registerCustomElement(string $alias, string $elementName) : void
    {
        $this->customElements[$alias] = array(
            'alias' => $alias,
            'name' => $elementName
        );

        HTML_QuickForm2_Factory::registerElement(
            $alias,
            ClassHelper::requireResolvedClass(HTML_QuickForm2_Element::class . '_' . $elementName)
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
     * @param array<string,mixed> $values
     * @return $this
     */
    public function setDefaultValues(array $values) : self
    {
        $this->log('Overwriting existing default values.');

        $this->defaultDataSource->setValues($values);

        return $this;
    }

    /**
     * @var array<string,HTML_QuickForm2_Element_InputHidden>
     */
    protected array $hiddens = array();

    /**
     * Selects the default element in the form. If possible, when the page is
     *  loaded, the field will automatically get focus.
     *
     * @param HTML_QuickForm2_Node $element
     * @return $this
     */
    public function setDefaultElement(HTML_QuickForm2_Node $element) : self
    {
        $this->ui->addJavascriptOnload("application.focusField('" . $element->getId() . "')");
        return $this;
    }

   /**
    * Sets an attribute of the form element itself.
    *
    * @param string $name
    * @param string|int|float|NULL $value
    * @return UI_Form
    */
    public function setAttribute(string $name, $value) : self
    {
        $this->form->setAttribute($name, $value);
        return $this;
    }

    /**
     * @return HTML_QuickForm2_Node[]
     */
    public function getErroneousElements(array $result=array()) : array
    {
        $elements = $this->form->getElements();

        foreach($elements as $element)
        {
            if($element->hasErrors()) {
                $result[] = $element;
            }

            if($element instanceof HTML_QuickForm2_Container) {
                $result = $this->getErroneousElements($element->getElements());
            }
        }

        return $result;
    }

    public function renderErrorMessages() : string
    {
        $elements = $this->getErroneousElements();

        $result = array();
        foreach($elements as $element)
        {
            $result[] = $element->getName().': '.$element->getError();
        }

        return '<ul>'.implode('</li><li>', $result).'</ul>';
    }

    /**
     * Manually submits the form given the specified data.
     *
     * @param array<string,mixed> $formValues
     * @return $this
     * @throws Application_Formable_Exception
     */
    public function makeSubmitted(array $formValues=array()) : self
    {
        $this->form->submitManually(new ManualSubmitDataSource($formValues));

        if(!$this->isSubmitted()) {
            throw new Application_Formable_Exception(
                'Form could not be submitted',
                'Setting the tracking var had no effect.',
                self::ERROR_COULD_NOT_SUBMIT_FORM
            );
        }

        return $this;
    }

    public function addGroupLayoutless(string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Container_Group
    {
        $container = $this->resolveContainer($container);

        $group = $container->addGroup($name);
        $group->setAttribute('rel', self::REL_LAYOUT_LESS_GROUP);

        return $group;
    }

    /**
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     * @see UI_Form_Renderer_CommentGenerator::addMarkdownComment()
     */
    public function addMarkdownSupport(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->setRuntimeProperty(UI_Form_Renderer_CommentGenerator::PROPERTY_MARKDOWN_SUPPORT, true);
        return $element;
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
     * @return HTML_QuickForm2_Node|null
     * @throws BaseClassHelperException
     */
    public function getElementByName(string $name) : ?HTML_QuickForm2_Node
    {
        $elements = $this->form->getElementsByName($name);

        if(!empty($elements))
        {
            return ClassHelper::requireObjectInstanceOf(
                HTML_QuickForm2_Node::class,
                $elements[0]
            );
        }

        return null;
    }

    public function requireElementByName(string $name) : HTML_QuickForm2_Node
    {
        $element = $this->getElementByName($name);

        if($element !== null) {
            return $element;
        }

        throw new FormException(
            'Required element not found',
            sprintf(
                'The form element with the name [%s] was not found.',
                $name
            ),
            self::ERROR_ELEMENT_NOT_FOUND
        );
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
    public function getRequiredElements(?HTML_QuickForm2_Container $container=null, array $result=array()) : array
    {
        $container = $this->resolveContainer($container);

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

    protected bool $silentValidation = false;

    /**
     * In silent validation mode, validation errors are not
     * displayed to the user, and the form does not add any
     * error messages to the UI.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setSilentValidation(bool $enabled=true) : self
    {
        $this->silentValidation = $enabled;
        return $this;
    }

   /**
    * Simulates the form being submitted using the form's current
    * values. This can be used to validate an arbitrary set of values
    * without needing to submit an actual form mask.
    *
    * To use this, create a form with the values you wish to validate
    * as default values, then validate the form as per usual.
    *
    * @return $this
    */
    public function simulateSubmit() : self
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

        return $this;
    }

    protected ?bool $validationResult = null;

    /**
     * Attempts to validate the form and returns the success state.
     *
     * Automatically adds a UI message to tell the user that something
     * is missing in the form, unless silent validation mode is enabled
     * ({@see self::setSilentValidation()}).
     *
     * @return boolean
     * @throws UI_Exception
     */
    public function validate() : bool
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
     * @throws UI_Exception
     */
    public function getInvalidElements(bool $simulateSubmit=false) : array
    {
        if($simulateSubmit) {
            $this->simulateSubmit();
        }

        if($this->validate()) {
            return array();
        }

        return $this->collectInvalidElements($this->form);
    }

    /**
     * @param HTML_QuickForm2_Container $container
     * @param HTML_QuickForm2_Node[] $collection
     * @return HTML_QuickForm2_Node[]
     */
    protected function collectInvalidElements(HTML_QuickForm2_Container $container, array $collection=array()) : array
    {
        $elements = $container->getElements();

        foreach($elements as $element) {
            if($element instanceof HTML_QuickForm2_Container) {
                $collection = $this->collectInvalidElements($element, $collection);
                continue;
            }

            if($element->hasErrors()) {
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
     * @throws UI_Exception
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
    public function getValues(bool $removeTrackingVar=false) : array
    {
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
    public function getImageUploaderElements() : array
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
    public function getTrackingName() : string
    {
        return '_qf__'.$this->form->getId();
    }

    public function isTrackingElement(HTML_QuickForm2_Node $element) : bool
    {
        return $element->getName() === $this->getTrackingName();
    }

    public function isDummyElement(HTML_QuickForm2_Node $element) : bool
    {
        return stripos($element->getName(), 'dummy') !== false;
    }

    protected function _render() : string
    {
        return $this->renderHorizontal();
    }

    public function renderHorizontal() : string
    {
        return $this->renderLayout('horizontal');
    }

   /**
    * @var UI_Form_Renderer|NULL
    */
    protected ?UI_Form_Renderer $formRenderer = null;

    /**
     * Renders the form to HTML using the form elements
     * form based on QuickForm's array renderer.
     *
     * @param string $layout
     * @return string
     */
    protected function renderLayout(string $layout) : string
    {
        if($this->readonly) {
            // do this again to ensure that all elements in the form get the info
            $this->makeReadonly();
        }

        $renderer = new UI_Form_Renderer($this, $this->form->renderToArray(), $layout);
        $renderer->setRegistryEnabled($this->clientRegistryEnabled);

        //$renderer->debugFormDef();

        $html = $renderer->render();

        $this->triggerEvent('rendered', array('renderer' => $renderer));
        $this->formRenderer = $renderer;

        return $html;
    }

    public function renderColumnized() : string
    {
        return $this->renderLayout('columnized');
    }

    protected bool $readonly = false;

   /**
    * Makes the form readonly so that it only shows element values,
    * without editing capabilities.
    *
    * @param bool $readonly
    * @return $this
    */
    public function makeReadonly(bool $readonly=true) : self
    {
        $this->readonly = $readonly;
        $this->form->toggleFrozen($readonly);

        return $this->toggleFormClass('frozen', $readonly);
    }

    public function isReadonly() : bool
    {
        return $this->readonly;
    }

    /**
     * @param string $class
     * @param bool $enabled
     * @return $this
     */
    protected function toggleFormClass(string $class, bool $enabled) : self
    {
        if($enabled) {
            $this->form->addClass($class);
        } else {
            $this->form->removeClass($class);
        }

        return $this;
    }

    /**
     * Makes the field labels wider to allow for longer labels.
     * @return $this
     */
    public function makeLabelsWider(bool $enabled=true) : self
    {
        return $this->toggleFormClass('wide-labels', $enabled);
    }

   /**
    * Turns the form into a more compact form layout.
    * @return $this
    */
    public function makeCondensed(bool $enabled=true) : self
    {
        return $this->toggleFormClass('form-condensed', $enabled);
    }

   /**
    * Marks the form as being collapsible: all headers within the
    * form will be rendered so that their contained form elements
    * can be collapsed/expanded at will.
    *
    * @return $this
    */
    public function makeCollapsible(bool $enabled=true) : self
    {
        return $this->toggleFormClass('form-collapsible', $enabled);
    }

    /**
     * Retrieves a format hint for any of the common
     * regexes. The name is the name of the regex constant
     * minus the <code>REGEX_</code> (case-insensitive),
     * so for example:
     *
     * getRegexHint('alias');
     * getRegexHint('name_or_title');
     *
     * @param string $name
     * @return string
     * @throws FormException
     */
    public static function getRegexHint(string $name) : string
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
                return (string)sb()
                    ->t('Must a a valid URL.')
                    ->t(
                        'If you omitted the %1$s, please add it.',
                        sb()->code('https://')
                    );

            case 'FILENAME':
                return (string)sb()
                    ->t('Allowed characters:')
                    ->t('Regular words, digits, spaces, dots (.), underscores (_), hyphens (-).')
                    ->t('Must start with a letter or number.');

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

        throw new FormException(
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
     * @param string|NULL $value
     * @return string
     */
    public function filter_adjustNumericNotation(?string $value) : string
    {
        return str_replace(array(' ', ','), array('', '.'), (string)$value);
    }

    /**
     * Retrieves the first element in the container's element collection,
     * or null if it does not have any elements.
     *
     * @param HTML_QuickForm2_Container $container
     * @return NULL|HTML_QuickForm2_Node
     */
    public function getFirstElement(HTML_QuickForm2_Container $container) : ?HTML_QuickForm2_Node
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
    protected function walkElements(HTML_QuickForm2_Container $container) : ?HTML_QuickForm2_Node
    {
        $elements = $container->getElements();

        foreach ($elements as $element)
        {
            if($element->getType() === 'hidden') {
                continue;
            }

            if ($this->isDummyElement($element)) {
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
    * @return $this
    */
    public function onSubmit(string $statement) : self
    {
        $this->form->setAttribute('onsubmit', $statement);
        return $this;
    }

    /**
     * @var array<string,bool>
     */
    protected array $elementIDs = array();

    /**
     * Creates an ID for a form element following the naming scheme standard
     * so that clientside scripts can access them easily as well.
     *
     * @param string $jsid
     * @param string|NULL $elementName
     * @return string
     * @throws FormException
     */
    public function createElementID(string $jsid, ?string $elementName) : string
    {
        $elementID = $jsid.'_field_'.str_replace(array('[', ']'), array('_', ''), $elementName);

        if(isset($this->elementIDs[$elementID])) {
            throw new FormException(
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
    * @param string|NULL $label
    * @return boolean
    */
    public static function validateLabel(?string $label) : bool
    {
        return preg_match(RegexHelper::REGEX_LABEL, (string)$label) === 1;
    }

   /**
    * Validates the specified string with the regex for
    * item aliases.
    *
    * @param string|NULL $alias
    * @return boolean
    */
    public static function validateAlias(?string $alias) : bool
    {
        return preg_match(RegexHelper::REGEX_ALIAS, $alias) === 1;
    }

   /**
    * Validates the specified string with the regex for
    * email addresses.
    *
    * @param string|NULL $email
    * @return boolean
    */
    public static function validateEmail(?string $email) : bool
    {
        return preg_match(RegexHelper::REGEX_EMAIL, (string)$email) === 1;
    }

    protected array $eventHandlers = array(
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
     * @return $this
     *
     * @throws Application_Exception
     * @throws FormException
     */
    public function addEventHandler(string $name, callable $handler) : self
    {
        if(!isset($this->eventHandlers[$name])) {
            throw new FormException(
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

    protected function triggerEvent(string $name, array $data=array()) : self
    {
        foreach($this->eventHandlers[$name] as $handler) {
            $handler($this, $data);
        }

        return $this;
    }

    // region: Adding element flavors

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws FormException
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
     * @throws FormException
     * @throws BaseClassHelperException
     */
    public function addTextarea(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Textarea
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Textarea::class,
            $this->addElement('textarea', $name, $container)
        )
            ->setLabel($label)
            ->addClass('input-xxlarge');
    }

    /**
     * Adds a subheader to the form, which does not contain any data.
     * It is purely cosmetic and rendered using the form renderer.
     *
     * @param string|number|StringableInterface|NULL $header
     * @param null|HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addSubheader($header, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Node
    {
        return $this->addDummyElement(toString($header), 'subheader', $container);
    }

    /**
     * Adds a collection of hidden variables to the form.
     *
     * NOTE: The ID of the generated elements can only be
     * specified when using the {@see self::addHiddenVar()} method.
     *
     * @param array<string,string|number|NULL> $vars Name => value pairs
     * @return $this
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function addHiddenVars(array $vars) : self
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, (string)$value);
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
     *
     * @throws BaseClassHelperException
     * @throws FormException
     * @throws UI_Exception
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
     *
     * @throws BaseClassHelperException
     * @throws FormException
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
     * @throws BaseClassHelperException
     * @throws FormException
     */
    public function addExpandableSelect(string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_ExpandableSelect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_ExpandableSelect::class,
            $this->addElement('ExpandableSelect', $name, $container)
        );
    }

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputFile
     *
     * @throws BaseClassHelperException
     * @throws FormException
     */
    public function addFile(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputFile
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_InputFile::class,
            $this->addElement('file', $name, $container)
        )
            ->setLabel($label);
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
     * @throws FormException
     */
    public function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container) : HTML_QuickForm2_Node
    {
        try
        {
            return $this->resolveContainer($container)
                ->addElement($type, $name);
        }
        catch (HTML_QuickForm2_Exception $e)
        {
            throw new FormException(
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
     * @param string|number|StringableInterface|NULL $label
     * @param string|number|StringableInterface|NULL $description
     * @return HTML_QuickForm2_Container_Group
     *
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addTab(string $name, $label, $description = null) : HTML_QuickForm2_Container_Group
    {
        $tab = $this->form->addGroup($name);
        $tab->setAttribute('rel', 'tab');
        $tab->setLabel(toString($label));
        $tab->setId(self::ID_PREFIX . $name);
        $tab->setAttribute('description', toString($description));

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
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     *
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

    /**
     * Adds an element for entering a hexadecimal color code.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addHexColor(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addText($name, $label, $container);
        $el->addFilterTrim();
        $el->addClass('input-small');
        $el->addClass('monospace');
        $el->setAttribute('data-type', 'hexcolor');

        $this->setElementPrepend($el, '#');
        $this->addRuleCallback(
            $el,
            function($value)
            {
                // this is handled by the field's required status
                if(empty($value)) {
                    return true;
                }

                return preg_match(RegexHelper::REGEX_HEX_COLOR_CODE, $value);
            },
            t('Not a valid hexadecimal color code.')
        );

        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_InputText::class,
            $el
        );
    }

    public function addStatic(string $label, string $content, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        $element = $this->addDummyElement($label, 'static', $container);
        $element->setAttribute('static_content', $content);

        return $element;
    }

    /**
     * Adds arbitrary HTML code to the form.
     *
     * @param string|int|float|bool|StringableInterface|NULL $html
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addHTML($html, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        return $this->addDummyElement(toString($html), 'html', $container);
    }

    /**
     * Adds a purely cosmetic hint message to the form, styled as an
     * informational message that has no data.
     *
     * @param string|number|StringableInterface|NULL $text
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addHint($text, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Node
    {
        return $this->addDummyElement(toString($text), 'hint', $container);
    }

    /**
     * Adds an ISO 8601 date element (YYYY-MM-DD).
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addISODate(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->resolveContainer($container)->addText($name);
        $el->addFilterTrim();
        $el->setLabel($label);
        $el->addClass('input-small');

        $this->addRuleISODate($el);

        return $el;
    }

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @param int $min
     * @param int $max
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
     */
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
     *
     * @param string|number|StringableInterface|NULL $text
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addParagraph($text, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Node
    {
        return $this->addDummyElement(toString($text), 'paragraph', $container);
    }

    /**
     * Adds a dummy element of the specified type with the specified content.
     * If no container is set, the form itself is used.
     *
     * @param string $content
     * @param string $type
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    protected function addDummyElement(string $content, string $type, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Element_InputText
    {
        $this->dummyCounter++;

        $element = $this->resolveContainer($container)->addElement('text', 'dummy' . $this->dummyCounter);
        $element->setLabel($content);
        $element->setAttribute('rel', $type);

        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_InputText::class,
            $element
        );
    }

    /**
     * Adds a hidden variable to the form that will get submitted along with visible fields.
     *
     * @param string $name
     * @param string|int|float|null $value
     * @param string|null $id
     * @return HTML_QuickForm2_Element_InputHidden
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function addHiddenVar(string $name, string|int|float|null $value = null, ?string $id = null) : HTML_QuickForm2_Element_InputHidden
    {
        if (!isset($this->hiddens[$name]))
        {
            $this->hiddens[$name] = $this->form->addHidden($name);
        }

        if($value === null) {
            $value = (string)$this->hiddens[$name]->getValue();
        } else {
            $value = (string)$value;
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
     *
     * @param string|AdminURLInterface $url
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws FormException
     * @throws BaseClassHelperException
     * @throws UI_Exception
     */
    public function addLinkButton($url, $label, $tooltip='') : HTML_QuickForm2_Element_UIButton
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
     * @throws FormException
     * @throws BaseClassHelperException
     * @throws UI_Exception
     * @throws BaseClassHelperException
     */
    public function addPrimarySubmit($label, string $name='save', $tooltip='') : HTML_QuickForm2_Element_UIButton
    {
        return $this->addSubmit($label, $name, $tooltip)
            ->makePrimary();
    }

    /**
     * Adds a primary styled button to the form's footer to submit it.
     * It automatically enables simulation mode before submitting
     * the form.
     *
     * @param string $label
     * @param string $name
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws BaseClassHelperException
     * @throws FormException
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
     *
     * @throws FormException
     * @throws BaseClassHelperException
     */
    public function addButton(string $name) : HTML_QuickForm2_Element_UIButton
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_UIButton::class,
            $this->addElement('uibutton', $name, $this->form)
        );
    }

    /**
     * Adds a button to the form's footer to submit it.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return HTML_QuickForm2_Element_UIButton
     *
     * @throws BaseClassHelperException
     * @throws FormException
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
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container Optional container to add the element to, defaults to the form itself.
     * @return HTML_QuickForm2_Element_Switch
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addSwitch(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Switch
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Switch::class,
            $this->resolveContainer($container)->addElement('switch', $name)
        )
            ->setLabel($label);
    }

    /**
     * Adds a tree selection element that uses a {@see \UI\Tree\TreeRenderer}
     * to display the item tree.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_TreeSelect
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addTreeSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_TreeSelect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_TreeSelect::class,
            $this
                ->resolveContainer($container)
                ->addElement(HTML_QuickForm2_Element_TreeSelect::ELEMENT_TYPE, $name)
        )
            ->setLabel($label);
    }

    /**
     * Adds a select element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Select
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Select::class,
            $this->resolveContainer($container)->addElement('select', $name)
        )
            ->setLabel($label);
    }

    /**
     * Adds a bootstrap multiselect element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Multiselect
     *
     * @throws FormException
     * @throws BaseClassHelperException
     */
    public function addMultiselect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Multiselect
    {
        return ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Element_Multiselect::class,
            $this->addElement('multiselect', $name, $container)
        )
            ->setLabel($label);
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
     * @throws BaseClassHelperException
     * @throws ConvertHelper_Exception
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
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
     * Adds an abstract text to the form.
     *
     * @param string $abstract
     * @param string[] $classes
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputText
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function addAbstract(string $abstract, array $classes=array(), ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        return $this->addHTML(
            '<p class="abstract form-abstract '.implode(' ', $classes).'">'.$abstract.'</p>',
            $container
        );
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
     * @throws FormException
     * @throws BaseClassHelperException
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
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleEmail(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Must be a valid e-mail address.'), RegexHelper::REGEX_EMAIL);
        $element->setAttribute('data-type', 'email');

        return $element;
    }

    public function addRuleURL(HTML_QuickForm2_Element $element): HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Must be a valid URL.'), RegexHelper::REGEX_URL);
        $element->setAttribute('data-type', 'url');

        return $element;
    }

    /**
     * Adds a phone number validation rule to the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRulePhone(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Must be a valid phone number.'), RegexHelper::REGEX_PHONE);
        $element->setAttribute('data-type', 'phone');
        return $element;
    }

    /**
     * Adds an alias validation rule to the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @param bool $allowCapitalLetters
     * @return HTML_QuickForm2_Node
     *
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleAlias(HTML_QuickForm2_Node $element, bool $allowCapitalLetters=false) : HTML_QuickForm2_Node
    {
        $regex = RegexHelper::REGEX_ALIAS;
        $dataType = 'alias';

        if($allowCapitalLetters)
        {
            $regex = RegexHelper::REGEX_ALIAS_CAPITALS;
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
     * @param HTML_QuickForm2_Node $element
     * @param callable $callback
     * @param string $errorMessage
     * @param mixed|array<int,mixed>|NULL $arguments Arguments for the callback, as an indexed array of parameters or a single value.
     * @return HTML_QuickForm2_Rule_Callback
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleCallback(HTML_QuickForm2_Node $element, callable $callback, string $errorMessage, $arguments=null) : HTML_QuickForm2_Rule_Callback
    {
        $rule = ClassHelper::requireObjectInstanceOf(
            HTML_QuickForm2_Rule_Callback::class,
            $element->addRule(
                'callback',
                $errorMessage,
                'trim'
            )
        );

        if(!empty($arguments))
        {
            if (!is_array($arguments)) {
                // this is to ensure the arguments are always passed on
                // and in the correct order, even if the arguments should
                // be null, since this may be intentional (There was an
                // empty check here before, which caused issues).
                $arguments = array($arguments);
            }
        } else {
            $arguments = array();
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
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleFilename(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Please enter a valid name.'), RegexHelper::REGEX_FILENAME);

        $element->setAttribute('data-type', 'filename');

        return $element;
    }

    /**
     * Adds an integer validation rule to the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @param int $min
     * @param int $max
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleInteger(HTML_QuickForm2_Node $element, int $min=0, int $max=0) : HTML_QuickForm2_Node
    {
        new UI_Form_Validator_Integer($this, $element, $min, $max);

        return $element;
    }

    /**
     * Adds a datetime validation rule to the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleISODate(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        new UI_Form_Validator_ISODate($this, $element);

        return $element;
    }

    /**
     * Adds a float validation rule to the element.
     *
     * @param HTML_QuickForm2_Node $element
     * @param float $min
     * @param float $max
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleFloat(HTML_QuickForm2_Node $element, float $min=0.0, float $max=0.0) : HTML_QuickForm2_Node
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
        $value = (string)$value;

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
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws BaseClassHelperException
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleDate(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
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
     * <pre>
     * $wrapper->addHeader('Title', null, 'AnchorName');
     * $wrapper->scrollToHeader('AnchorName');
     * </pre>
     *
     * @param string $anchorName
     * @return $this
     */
    public function scrollToHeader(string $anchorName) : self
    {
        $this->ui->addJavascriptOnload("setTimeout(function() {UI.ScrollToElement('#" . $anchorName . "');}, 1000)");
        return $this;
    }

    /**
     * Adds a redactor to the target element, and returns the
     * redactor helper instance for additional configuration.
     *
     * @param HTML_QuickForm2_Element $element
     * @param Application_Countries_Country $country
     * @return UI_MarkupEditor_Redactor
     *
     * @throws BaseClassHelperException
     */
    public function makeRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_Redactor
    {
        return ClassHelper::requireObjectInstanceOf(
            UI_MarkupEditor_Redactor::class,
            $this->makeMarkupEditor('Redactor', $element, $country)
        );
    }

    public function makeCKEditor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_CKEditor
    {
        return ClassHelper::requireObjectInstanceOf(
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
    * Post-validation routine: gives elements the time to post-process
    * a valid form. This is used, for example, for the image elements so
    * uploaded images can be automatically transformed into regular media
    * documents once the form is valid and will be processed.
    *
    * @see HTML_QuickForm2_Element_ImageUploader::upgradeMedia()
    */
    protected function postValidation() : void
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
     * The callback function gets an instance of {@see UI_Form_Renderer_Element}.
     *
     * @param HTML_QuickForm2_Node $element
     * @param callable $callback
     *
     * @throws FormException
     * @throws Application_Exception
     *
     * @see UI_Form_Renderer_Element
     */
    public function addRenderCallback(HTML_QuickForm2_Node $element, callable $callback) : void
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
     *
     * @throws ConvertHelper_Exception
     */
    public function makeStructural(HTML_QuickForm2_Node $el, bool $structural=true) : HTML_QuickForm2_Node
    {
        $el->setAttribute('structural', ConvertHelper::bool2string($structural, true));
        return $el;
    }

    /**
     * Sets the element to be rendered as a standalone element:
     * this hides the label, and removes the element's indentation,
     * so it can use the full available width.
     *
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     */
    public function makeStandalone(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->setAttribute('standalone', 'yes');
        return $element;
    }

    /**
     * Adds a validation rule to make the element required.
     *
     * @param HTML_QuickForm2_Node $el
     * @param string|number|StringableInterface|NULL $message
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     * @throws UI_Exception
     */
    public function makeRequired(HTML_QuickForm2_Node $el, $message=null) : HTML_QuickForm2_Node
    {
        $message = toString($message);

        if(empty($message))
        {
            if($el instanceof HTML_QuickForm2_Element_Select) {
                $message = t('Please select a value.');
            } else {
                $message = t('Please enter a value.');
            }
        }

        $el->addRule('required', toString($message));
        $el->setAttribute('data-required', 'true');

        return $el;
    }

    /**
     * Adds a validation rule to the element to limit the length
     * to the specified number of characters.
     *
     * Automatically adds a validation hint for the length as well.
     *
     * Note: To limit to a specific length, set the min and max
     * to the same value.
     *
     * @param HTML_QuickForm2_Node $el
     * @param int|NULL $min
     * @param int|NULL $max
     * @return HTML_QuickForm2_Node
     *
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
     */
    public function makeLengthLimited(HTML_QuickForm2_Node $el, ?int $min, ?int $max) : HTML_QuickForm2_Node
    {
        $min = (int)$min;
        $max = (int)$max;

        if($min > $max) {
            throw new FormException(
                'Invalid length',
                sprintf(
                    'The minimum length [%s] is higher than the maximum [%s].',
                    $min,
                    $max
                ),
                self::ERROR_INVALID_LENGTH_LIMIT
            );
        }

        if($min === $max) {
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
     *
     * @throws FormException
     * @throws HTML_QuickForm2_Exception
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
            throw new FormException(
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
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     */
    public function makeHiddenWhenReadonly(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->setAttribute('data-hidden-when-frozen', 'yes');
        return $element;
    }

    /**
     * Appends the related units for the element's values to the
     * element in the UI.
     *
     * Example: "Centimetres".
     *
     * @param HTML_QuickForm2_Node $element
     * @param string|number|StringableInterface|NULL $units
     * @return HTML_QuickForm2_Node
     *
     * @throws UI_Exception
     */
    public function setElementUnits(HTML_QuickForm2_Node $element, $units) : HTML_QuickForm2_Node
    {
        return $this->setElementAppend($element, $units);
    }

    public function addFilterComma2Dot(HTML_QuickForm2_Element $element) : HTML_QuickForm2_Element
    {
        $element->addFilter(array($this, 'callback_convertComma'));

        return $element;
    }

    /**
     * Adds a string to append to an element.
     * For example for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Node $element
     * @param string|number|StringableInterface|NULL $appendString
     * @return HTML_QuickForm2_Node
     *
     * @throws UI_Exception
     */
    public function setElementAppend(HTML_QuickForm2_Node $element, $appendString) : HTML_QuickForm2_Node
    {
        $element->setAttribute('data-append', toString($appendString));
        return $element;
    }

    /**
     * Adds a string to prepend to an element.
     * For example for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Node $element
     * @param string|number|StringableInterface $prependString
     * @return HTML_QuickForm2_Node
     *
     * @throws UI_Exception
     */
    public function setElementPrepend(HTML_QuickForm2_Node $element, $prependString) : HTML_QuickForm2_Node
    {
        $element->setAttribute('data-prepend', toString($prependString));
        return $element;
    }

    /**
     * @param string $position
     * @param HTML_QuickForm2_Node $element
     * @param string|number|StringableInterface $html
     * @param bool $whenFrozen
     * @return HTML_QuickForm2_Node
     */
    protected function addElementHTML(string $position, HTML_QuickForm2_Node $element, $html, bool $whenFrozen=false) : HTML_QuickForm2_Node
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
    * Not to mistake with {@link self::setElementAppend()}.
    *
    * @param HTML_QuickForm2_Node $element
    * @param string|number|StringableInterface $html
    * @param boolean $whenFrozen Whether to display this even when the element is frozen.
    * @return HTML_QuickForm2_Node
    *
    * @see self::prependElementHTML()
    * @see self::setElementAppend()
    */
    public function appendElementHTML(HTML_QuickForm2_Node $element, $html, bool $whenFrozen=false) : HTML_QuickForm2_Node
    {
        return $this->addElementHTML('append', $element, $html, $whenFrozen);
    }

   /**
    * Appends a button after the element's input.
    *
    * @param HTML_QuickForm2_Node $element
    * @param UI_Button|UI_Bootstrap $button
    * @param boolean $whenFrozen Whether to display this even when the element is frozen.
    * @return HTML_QuickForm2_Node
    */
    public function appendElementButton(HTML_QuickForm2_Node $element, $button, bool $whenFrozen=false) : HTML_QuickForm2_Node
    {
        $button->addClass('after-input');

        return $this->appendElementHTML($element, $button, $whenFrozen);
    }

    /**
     * Appends a button to the element to generate an alias from the content
     * of the target element. Uses the AJAX transliterate function to create
     * the alias from a string.
     *
     * @param HTML_QuickForm2_Node $aliasElement
     * @param HTML_QuickForm2_Node $fromElement
     * @return HTML_QuickForm2_Node
     *
     * @throws JSONConverterException
     */
    public function appendGenerateAliasButton(HTML_QuickForm2_Node $aliasElement, HTML_QuickForm2_Node $fromElement) : HTML_QuickForm2_Node
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
     * Not to mistake with {@see self::setElementPrepend()}.
     *
     * @param HTML_QuickForm2_Node $element
     * @param string|number|StringableInterface $html
     * @param bool $whenFrozen
     * @return HTML_QuickForm2_Node
     *
     * @see self::appendElementHTML()
     * @see self::setElementPrepend()
     */
    public function prependElementHTML(HTML_QuickForm2_Node $element, $html, bool $whenFrozen=false) : HTML_QuickForm2_Node
    {
        return $this->addElementHTML('prepend', $element, $html, $whenFrozen);
    }

    // endregion

    /**
     * Adds a label validation rule to the element.
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleLabel(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Must be a valid label.'), RegexHelper::REGEX_LABEL);
        $element->setAttribute('data-type', 'label');
        return $element;
    }

    /**
     * Adds a name or title validation rule to the element.
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleNameOrTitle(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', t('Must be a valid title.'), RegexHelper::REGEX_NAME_OR_TITLE);
        $element->setAttribute('data-type', 'name_or_title');
        return $element;
    }

    /**
     * Adds a rule that disallows using HTML in the element.
     * @param HTML_QuickForm2_Node $element
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleNoHTML(HTML_QuickForm2_Node $element) : HTML_QuickForm2_Node
    {
        $element->addRule('notregex', t('May not contain HTML.'), RegexHelper::REGEX_IS_HTML);
        $element->setAttribute('data-type', 'nohtml');
        return $element;
    }

    /**
     * Adds a rule to confirm the input to the specified regex.
     *
     * @param HTML_QuickForm2_Node $element
     * @param string $regex
     * @param string $message
     * @return HTML_QuickForm2_Node
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function addRuleRegex(HTML_QuickForm2_Node $element, string $regex, string $message) : HTML_QuickForm2_Node
    {
        $element->addRule('regex', $message, $regex);

        return $element;
    }

   /**
    * Parses a date string into a date object. The date must have
    * a format matching the  {@see RegexHelper::REGEX_DATE} regular
    * expression.
    *
    * @param string|NULL $dateString
    * @return NULL|DateTime
    */
    public static function parseDate(?string $dateString) : ?DateTime
    {
        $matches = null;
        preg_match_all('%([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4}) ([0-9]{1,2}):([0-9]{1,2})|([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})%', (string)$dateString, $matches, PREG_PATTERN_ORDER);

        if(empty($matches[0])) {
            return null;
        }

        try{
            $date = new DateTime($matches[0][0]);
        } catch(Exception $e) {
            return null;
        }

        return $date;
    }

    protected string $title = '';

    /**
     * Sets the title of the form. This is typically used
     * in the form rendering template as title for the content
     * section in which the form is shown.
     *
     * @param string|number|StringableInterface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        $this->title = toString($title);
        return $this;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    protected string $abstract = '';

    /**
     * Sets the abstract of the form. This is typically used
     * in the form rendering template as title for the content
     * section in which the form is shown.
     *
     * @param string|number|StringableInterface|NULL $abstract
     * @return $this
     * @throws UI_Exception
     */
    public function setAbstract($abstract) : self
    {
        $this->abstract = toString($abstract);
        return $this;
    }

    public function getAbstract() : string
    {
        return $this->abstract;
    }

    /**
     * Retrieves the matching UI_Form instance for the specified
     * QuickForm element, or NULL if it could not be found.
     *
     * @param HTML_QuickForm2_Node $el
     * @return UI_Form|NULL
     * @throws FormException
     */
    public static function getInstanceByElement(HTML_QuickForm2_Node $el) : ?UI_Form
    {
        $id = $el->getId();
        if(empty($id)) {
            throw new FormException(
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

    public function hasElements(bool $includeHiddens=false) : bool
    {
        $elements = $this->getForm()->getElements();

        foreach ($elements as $element) {
            if(!$includeHiddens && $element->getType() === 'hidden') {
                continue;
            }

            return true;
        }

        return false;
    }

    public function getName() : string
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
     * @throws Application_Formable_Exception
     * @throws ConvertHelper_Exception
     * @throws FormException
     */
    public function getJSSubmitHandler(bool $simulate=false) : string
    {
        return self::renderJSSubmitHandler($this, $simulate);
    }

    /**
     * @param class-string|UI_DataGrid|UI_Form|Application_Formable|Application_Interfaces_Formable|mixed $subject
     * @return string
     * @throws Application_Formable_Exception
     */
    public static function resolveFormName($subject) : string
    {
        if($subject instanceof UI_DataGrid)
        {
            return $subject->getFormID();
        }

        if($subject instanceof self)
        {
            return $subject->getName();
        }

        if($subject instanceof Application_Interfaces_Formable)
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
     *
     * @throws Application_Formable_Exception
     * @throws ConvertHelper_Exception
     * @throws FormException
     */
    public static function renderJSSubmitHandler($subject, bool $simulate=false) : string
    {
        $formName = self::resolveFormName($subject);

        if(!empty($formName))
        {
            return sprintf(
                "application.submitForm('%s', %s)",
                $formName,
                ConvertHelper::bool2string($simulate)
            );
        }

        throw new FormException(
            'Unhandled submit subject',
            sprintf(
                'The subject of type [%s] is not compatible with the available form types.',
                gettype($subject)
            ),
            self::ERROR_UNHANDLED_SUBMIT_HANDLER_SUBJECT
        );

    }

    protected bool $clientRegistryEnabled = false;

   /**
    * Enables or disables the clientside form elements registry: this
    * is an easy way to access information on the form on the client
    * side, from sections to individual elements. By default, this is
    * disabled.
    *
    * @param bool $enabled
    * @return $this
    */
    public function enableClientRegistry(bool $enabled=true) : self
    {
        if(isset($this->formRenderer))
        {
            $this->formRenderer->setRegistryEnabled($enabled);
        }

        $this->clientRegistryEnabled = $enabled;

        return $this;
    }

    /**
     * When there are no "submit" buttons directly in a form,
     * we can make it possible to submit it via the enter key by
     * adding this invisible "submit" button. The div does not
     * use visibility or display to hide it, since Google
     * Chrome will not accept the enter key for a hidden
     * "submit" element.
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

    public function compileExamples() : string
    {
        return
        t('Examples:').' '.
        '<span class="form-example-string">'.
            implode('</span>, <span class="form-example-string">', func_get_args()).
        '</span>';
    }

    public function compileValues() : string
    {
        return
        t('Possible values:').' '.
        '<span class="form-example-string">'.
            implode('</span>, <span class="form-example-string">', func_get_args()).
        '</span>';
    }

    public function resolveContainer(?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Container
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
