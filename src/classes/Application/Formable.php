<?php
/**
 * File containing the {@link Application_Formable} class.
 * @package Application
 * @subpackage Forms
 * @see Application_Formable
 */

use Application\Exception\UnexpectedInstanceException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\RegexHelper;

/**
 * Utility class for classes that create forms: offers a standardized API
 * for instantiating QuickForm2 form elements, with productivity related
 * methods.
 *
 * Usage:
 * 
 * <ul>
 *    <li>Extend this class</li>
 *    <li>Call the <code>createFormableForm()</code> method to set up the form environment</li>
 *    <li>Build the form</li>
 * </ul>
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Formable implements Application_Interfaces_Formable
{
    public const ERROR_FORMABLE_NOT_INITIALIZED = 38732001;
    public const ERROR_INVALID_RULE_OPERATOR = 38732002;
    public const ERROR_ELEMENT_NOT_FOUND_BY_NAME = 38732003;
    public const ERROR_NO_PAGE_INSTANCE = 38732004;
    public const ERROR_FORM_NOT_VALID = 38732005;

   /**
    * @var UI_Form
    */
    protected $formableForm;
    
    protected ?HTML_QuickForm2_Container $formableContainer = null;

   /**
    * This is always the main form object.
    * @var HTML_QuickForm2_Container
    */
    protected $formableMainContainer;
    
   /**
    * @var string
    */
    protected $formableJSID;
    
   /**
    * @var boolean
    */
    protected $formableLogging = false;
    
   /**
    * Creates a form to use specifically for clientside forms.
    * These require the JSID to be set, and ensure that any
    * unnecessary elements are stripped.
    * 
    * Additionally, a dummy UI object is used to capture only
    * the javascript required by the form so it can be sent 
    * along with the form.
    * 
    * @param string $name
    * @param array $defaultData
    * @return UI_Form
    */
    protected function createClientForm(string $name, array $defaultData=array()) : UI_Form
    {
        $ui = UI::selectDummyInstance();
        
        $form = $ui->createForm($name, $defaultData);
        $form->getForm()
        ->setAttribute('is-client-form', 'yes');
        
        return $form;
    }
    
   /**
    * Creates a regular, non clientside form.
    * 
    * @param string $name
    * @param array<string,mixed> $defaultData
    * @return UI_Form
    */
    protected function createForm(string $name, array $defaultData=array()) : UI_Form
    {
        return UI::getInstance()->createForm($name, $defaultData);
    }
    
   /**
    * Creates a serverside form and initializes the formable
    * with the form object. This is a shorthand for doing both
    * of these operations manually using {@link createForm()}
    * and {@link initFormable()}.
    *
    * @param string $name
    * @param array $defaultData
    */
    public function createFormableForm(string $name, array $defaultData=array())
    {
        $form = $this->createForm($name, $defaultData);
        $this->initFormable($form);
    }
    
    protected bool $formableInitialized = false;
    
   /**
    * @var UI
    */
    protected UI $ui;
    
   /**
    * Initializes the form management with the specified, previously created form object.
    * Note that the form should already have its values set at this point!
    * 
    * @param UI_Form $form
    * @param HTML_QuickForm2_Container|NULL $defaultContainer The default container to which to add elements to, defaults to the form itself
    * @return Application_Formable
    */
    protected function initFormable(UI_Form $form, ?HTML_QuickForm2_Container $defaultContainer=null) : Application_Formable
    {
        if($this->formableInitialized) {
            return $this;
        }
        
        $this->formableInitialized = true;
        $this->formableForm = $form;
        $this->formableMainContainer = $form->getForm();
        $this->formableContainer = $defaultContainer;
        $this->ui = $form->getUI();
        
        $this->formableMainContainer->setAttribute('data-jsid', $form->getJSID());
        
        $this->formableJSID = $form->getJSID();
        
        $this->formableForm->addClass('form-horizontal');
        
        // clean up the form if it is intended for clientside use
        if($this->formableMainContainer->getAttribute('is-client-form') === 'yes')
        {
            // the UI_Form automatically adds the "page" hidden element,
            // we don't want that in our data set.
            $page = $this->formableForm->getElementByID('f-page');
            if($page) {
                //$this->formableMainContainer->removeChild($page);
            }
        }
        
        if(!empty($this->containers))
        {
            $this->logFormable(sprintf(
                'Cascading initialization to [%s] containers.', 
                count($this->containers)
            ));
            
            foreach($this->containers as $container) {
                $container->handleFormableInitialized();
            }
        }
        
        if($this instanceof Application_Admin_ScreenInterface)
        {
            $this->addFormablePageVars();
        }
        
        $this->logFormable('Initialization complete.');
        
        return $this;
    }

    public function getInstanceID() : string
    {
        return $this->getFormableInstanceID();
    }

    public function getFormableJSID() : string
    {
        return $this->formableJSID;
    }
    
    protected $formableInstanceID;
    
    public function getFormableInstanceID()
    {
        if(!isset($this->formableInstanceID)) {
            $this->formableInstanceID = nextJSID();
        }
        
        return $this->formableInstanceID;
    }
    
   /**
    * Ensures that the formable has been initialized. This should
    * be used for all operations that are done after the initialization.
    * 
    * @throws Application_Formable_Exception See {@see self::ERROR_FORMABLE_NOT_INITIALIZED}
    */
    protected function requireFormableInitialized() : void
    {
        if(isset($this->activeHeader)) {
            $this->activeHeader->apply();
        }
        
        if($this->formableInitialized) {
           return; 
        }
        
        throw new Application_Formable_Exception(
            'The formable has not been initialized',
            (string)sb()
            ->sf(
                'The formable [%s] has to be initialized for this operation.', 
                $this->getFormableInstanceID()
            )
            ->add('This can usually be fixed by ensuring that createFormableForm() is called before this operation.'),
            self::ERROR_FORMABLE_NOT_INITIALIZED
        );
    }
    
   /**
    * Sets the name of the form's default element.
    * Adds a data attribute to the form that can be used clientside
    * to determine the default element.
    * 
    * @param string|HTML_QuickForm2_Node $elementNameOrObject
    * @return Application_Formable
    */
    public function setDefaultElement($elementNameOrObject) : self
    {
        $this->requireFormableInitialized();
        
        $element = $elementNameOrObject;
        
        if(!$element instanceof HTML_QuickForm2_Node)
        {
            $element = $this->getElementByName($elementNameOrObject);
        }

        if($element !== null)
        {
            $this->getFormableContainer()->setAttribute(
                'data-default-element',
                $elementNameOrObject
            );
            
            $this->formableForm->setDefaultElement($element);
        }
        
        return $this;
    }

    // region: A - Adding elements

    public function addElementSwitch(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Switch
    {
        $el = $this->getFormInstance()->addSwitch($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElementTreeSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_TreeSelect
    {
        $el = $this->getFormInstance()->addTreeSelect($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElementInteger(string $name, string $label, ?HTML_QuickForm2_Container $container=null, int $min=0, int $max=0) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addInteger($name, $label, $this->getFormableDefaultContainer($container), $min, $max);

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElementISODate(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addISODate($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElementDatepicker(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->getFormInstance()->addDatepicker($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    /**
     * Creates a form element to enter a percentage value.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementPercent(string $name, string $label, ?HTML_QuickForm2_Container $container=null, float $min=0, float $max=100) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addPercent($name, $label, $container, $min, $max);

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Node
    {
        $el = $this->getFormInstance()->addElement($type, $name, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
    /**
     * Adds a text element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementText(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addText($name, $label, $container);
        
        $this->registerFormableElement($el);
        
        return $el;
    }
    
    public function addElementHexColor(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addHexColor($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a textarea element.
    * 
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_Textarea
    */
    public function addElementTextarea(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Textarea
    {
        $el = $this->getFormInstance()->addTextarea($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a custom HTML container element that will be output
    * at the element's position in the form.
    * 
    * @param string $html
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_InputText
    */
    public function addElementHTML(string $html, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addHTML($html, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    public function addElementAbstract(string $abstract, array $classes=array(), ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addAbstract($abstract, $classes, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
    protected ?Application_Formable_Header $activeHeader = null;
    
   /**
    * @deprecated Use {@see self::addSection()} instead.
    */
    public function addElementHeaderII($label) : Application_Formable_Header
    {
        return $this->addSection($label);
    }

    /**
     * Adds a section to hold elements in the form.
     *
     * @param string|number|StringableInterface|NULL $label
     * @return Application_Formable_Header
     */
    public function addSection($label) : Application_Formable_Header
    {
        $this->requireFormableInitialized();

        $header = new Application_Formable_Header($this, $label);

        $this->activeHeader = $header;

        return $header;
    }

    /**
     * Adds a tab to hold elements in the form.
     *
     * This is a form container object, which must be passed
     * on to all elements that you wish to show there.
     *
     * @param string $name
     * @param string|number|StringableInterface|NULL $label
     * @param string|number|StringableInterface|NULL $description
     * @return HTML_QuickForm2_Container_Group
     * @throws Application_Formable_Exception
     */
    public function addTab(string $name, $label, $description=null) : HTML_QuickForm2_Container_Group
    {
        $el = $this->getFormInstance()->addTab($name, $label, $description);

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a file upload element.
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_InputFile
    */
    public function addElementFile(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputFile
    {
        $el = $this->getFormInstance()->addFile($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a plupload-powered image uploader element that uses the application
    * media management classes to handle the uploaded images.
    * 
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_ImageUploader
    */
    public function addElementImageUploader(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_ImageUploader
    {
        $el = $this->getFormInstance()->addImageUploader($name, $container);
        $el->setLabel($label);
        $el->setComment(t('May only contain letters, numbers and the characters - and _.'));

        $this->registerFormableElement($el);

        return $el;
    }

    /**
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_ExpandableSelect
     *
     * @throws Application_Formable_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function addElementExpandableSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_ExpandableSelect
    {
        $el = $this->getFormInstance()->addExpandableSelect($name, $container);
        $el->setLabel($label);

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a visual select element, that lets users select values by
    * clicking images additionally to selecting from a dropdown.
    * 
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_VisualSelect
    */
    public function addElementVisualSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_VisualSelect
    {
        $el = $this->getFormInstance()->addVisualSelect($name, $container);
        $el->setLabel($label);

        $this->registerFormableElement($el);
        
        return $el;
    }

    /**
     * Adds a select element. Use the element's API to add the
     * available options after adding it.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Select
     * @throws Application_Formable_Exception
     */
    public function addElementSelect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Select
    {
        $el = $this->getFormInstance()->addSelect($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds an element with static HTML content. Not to mistake with 
    * the HTML element, which works the same but has no label.
    * 
    * @param string $label
    * @param string $content
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_InputText
    */
    public function addElementStatic(string $label, string $content, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addStatic($label, $content, $container);

        $this->registerFormableElement($el);
        
        return $el;
    }
    
   /**
    * Adds a multiselect select element with search capabilities.
    * 
    * @param string $name
    * @param string $label
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Element_Multiselect
    */
    public function addElementMultiselect(string $name, string $label, ?HTML_QuickForm2_Container $container=null) : HTML_QuickForm2_Element_Multiselect
    {
        $el = $this->getFormInstance()->addMultiselect($name, $label, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds an element to the form that will be rendered as a heading.
    * 
    * NOTE: You may prefer using the {@link addElementHeaderII} method
    * instead, which is easier to use.
    * 
    * @param string $title
    * @param HTML_QuickForm2_Container|NULL $container
    * @param string|NULL $anchor The name of the anchor that can be used to jump to the heading
    * @param boolean $collapsed Whether the header should start collapsed
    * @return HTML_QuickForm2_Element_InputText
    * @see Application_Formable::addSection()
    * @deprecated Use {@see self::addSection()} instead.
    */
    public function addElementHeader(string $title, ?HTML_QuickForm2_Container $container = null, ?string $anchor=null, bool $collapsed=true) : HTML_QuickForm2_Element_InputText
    {
        $el = $this->getFormInstance()->addHeader($title, $this->getFormableDefaultContainer($container), $anchor, $collapsed);

        $this->registerFormableElement($el);

        return $el;
    }
    
   /**
    * Adds a group to contain elements, but which does not generate any layout.
    * Use this if you have to namespace element names in case there are duplicate
    * element names.
    * 
    * @param string $name
    * @param HTML_QuickForm2_Container|NULL $container
    * @return HTML_QuickForm2_Container_Group
    */
    public function addElementGroupLayoutless(string $name, ?HTML_QuickForm2_Container $container = null) : HTML_QuickForm2_Container_Group
    {
        $el = $this->getFormInstance()->addGroupLayoutless($name, $this->getFormableDefaultContainer($container));

        $this->registerFormableElement($el);

        return $el;
    }

    // endregion

    public function getFormableDefaultContainer(?HTML_QuickForm2_Container $container=null) : ?HTML_QuickForm2_Container
    {
        if($container !== null) {
            return $container;
        }

        return $this->formableContainer;
    }

   /**
    * Adds a hidden variable field to the form.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    public function addHiddenVar(string $name, string $value='') : self
    {
        $this->requireFormableInitialized();
        
        $el = $this->getElementByName($name);
        if($el)
        {
            $el->setValue($value); 
        }
        else
        {
            $this->formableForm->addHiddenVar($name, $value, $this->getElementID($name));
        }
        
        return $this;
    }
    
   /**
    * Adds a collection of hidden form variables.
    * 
    * Example:
    * 
    * <pre>
    * addHiddenVars(
    *    array(
    *       'variable1' => 'Value',
    *       'variable2' => 'Value'
    *    )
    * );
    * </pre>
    * 
    * @param array<string,string> $vars Associative array with variable name => value pairs.
    * @return $this
    */
    public function addHiddenVars(array $vars) : self
    {
        foreach($vars as $var => $value) 
        {
            $this->addHiddenVar($var, $value);
        }
        
        return $this;
    }
     
   /**
    * Retrieves the ID for an element given its name.
    * @param string $name
    * @return string
    * @see UI_Form::createElementID()
    */
    protected function getElementID($name)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->createElementID($this->formableJSID, $name);
    }
    
    public function renderFormable()
    {
        $this->requireFormableInitialized();
        
        $html = $this->formableForm->renderHorizontal();
        
        if($this->formableMainContainer->getAttribute('is-client-form') === 'yes') {
            $ui = $this->formableForm->getUI();
            $html = $ui->renderHeadIncludes().$html;
        }
        
        return $html;
    }

   /**
    * Creates an element ID using the specified client jsID
    * so the clientside application can access these elements
    * by their ID. 
    * 
    * NOTE: This is not necessary for form fields, those are
    * handled automatically. This is only for custom DOM elements.
    * 
    * @param string $part
    * @return string
    */
    protected function getClientElementID($part)
    {
        $this->requireFormableInitialized();
        
        return $this->formableJSID .'_' . $part;
    }
    
   /**
    * Retrieves the default value for image uploader elements.
    * This is an array with a specific structure, so to avoid
    * having to create it manually it is recommended to use this
    * method.
    * 
    * @return array
    * @see HTML_QuickForm2_Element_ImageUploader::getDefaultData()
    */
    protected function getDefaultImageUploaderValue()
    {
        return HTML_QuickForm2_Element_ImageUploader::getDefaultData();
    }

    // region: B - Adding rules
    
    public function addRulePhone(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRulePhone($element);
    }
    
    public function addRuleEmail(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleEmail($element);
    }

    public function addRuleAlias(HTML_QuickForm2_Element $element, $allowCapitalLetters=false)
    {
        $this->requireFormableInitialized();
    
        return $this->formableForm->addRuleAlias($element, $allowCapitalLetters);
    }
    
   /**
    * Adds a callback rule. Helper method for easier access to
    * the QuickForm API for this. The first argument of the callback
    * is always the value to validate, and the last is the
    * rule object instance, even if custom arguments are specified.
    * 
    * @param HTML_QuickForm2_Node $element
    * @param callable $callback
    * @param string $errorMessage
    * @param mixed[] $arguments Indexed list of arguments for the callback
    * @return HTML_QuickForm2_Rule_Callback
    */
    public function addRuleCallback(HTML_QuickForm2_Node $element, $callback, string $errorMessage, array $arguments=array()) : HTML_QuickForm2_Rule_Callback
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleCallback($element, $callback, $errorMessage, $arguments);
    }
    
    public function addRuleLabel(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleLabel($element);
    }
    
   /**
    * Adds a rule to enter a filename.
    * 
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleFilename(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleFilename($element);
    }

   /**
    * Adds a rule to validate as name or title, which is less
    * restrictive than the label rule.
    * 
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleNameOrTitle(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleNameOrTitle($element);
    }
    
   /**
    * Adds a validation rule to the element that disallows
    * using HTML in its content.
    * 
    * @param HTML_QuickForm2_Element $element
    * @return HTML_QuickForm2_Element
    */
    public function addRuleNoHTML(HTML_QuickForm2_Element $element) : HTML_QuickForm2_Element
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleNoHTML($element);
    }
    
    public function addRuleRegex(HTML_QuickForm2_Element $element, string $regex, string $message)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleRegex($element, $regex, $message);
    }
    
   /**
    * Adds an integer validation rule to the element, with the 
    * possiblity to set a minimum and/or maximum value. Automatically
    * adds validation hints to the element comments.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param integer $min
    * @param integer $max
    * @return HTML_QuickForm2_Element
    */
    public function addRuleInteger(HTML_QuickForm2_Element $element, int $min=0, int $max=0)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleInteger($element, $min, $max);
    }
    
    /**
     * Adds a date time validation to the element.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleISODate(HTML_QuickForm2_Element $element)
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm->addRuleISODate($element);
    }
    
   /**
    * Adds a rule that the element should be required if
    * the other element's value matches the specified value.
    * 
    * @param HTML_QuickForm2_Node $element
    * @param HTML_QuickForm2_Node $otherElement
    * @param mixed $otherValue
    * @param string $operator The operator to use for the comparison
    * @see HTML_QuickForm2_Rule_Compare
    */
    public function addRuleRequiredIfOther(HTML_QuickForm2_Node $element, $message, HTML_QuickForm2_Node $otherElement, $otherValue, $operator='==')
    {
        if(empty($operator)) {
            $operator = '==';
        }
        
        $operators = HTML_QuickForm2_Rule_Compare::getOperators();
        
        if(!in_array($operator, $operators)) {
            throw new Application_Exception(
                'Invalid operator',
                sprintf(
                    'The operator [%s] is not a valid comparison operator. Valid operators are [%s].',
                    $operator,
                    implode(', ', $operators)
                ),
                self::ERROR_INVALID_RULE_OPERATOR
            );
        }
        
        $otherHasValue = $otherElement->createRule('eq', '', array());
        $otherHasValue->setConfig(array($operator, $otherValue));
        
        $required = $element->createRule('nonempty', $message);
        
        $element->setAttribute('data-required', 'true');
        
        $otherElement->addRule($otherHasValue)->and_($required);
    }
    
   /**
    * Adds a rule to make the element required if the 
    * other element's value is not empty.
    * 
    * @param HTML_QuickForm2_Node $element
    * @param HTML_QuickForm2_Node $otherElement
    * @param string|NULL $message A specific message, to overwrite the default message
    */
    public function addRuleRequiredIfOtherNonEmpty(HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $otherElement, $message=null)
    {
        if(empty($message)) {
            $message = t('Required if %1$s is not empty.', $otherElement->getLabel());
        }
        
        $this->addRuleRequiredIfOther($element, $message, $otherElement, '', '!=');
    }
    
    public function addRuleFloat(HTML_QuickForm2_Element $element, float $min=0, float $max=0)
    {
        return $this->getFormInstance()->addRuleFloat($element, $min, $max);
    }

    // endregion
    



    
   /**
    * Registers an element with the formable. This is done automatically
    * with all elements created with the formable methods, but can be
    * used to register elements created any other way.
    * 
    * @param HTML_QuickForm2_Node $el
    * @return HTML_QuickForm2_Node
    */
    protected function registerFormableElement(HTML_QuickForm2_Node $el) : HTML_QuickForm2_Node
    {
        $el->setId($this->formableForm->createElementID($this->formableJSID, $el->getName()));
        return $el;
    }
    

    
   /**
    * Retrieves the form's values as an associative array
    * with element name => value pairs.
    * 
    * @return array<string,mixed>
    */
    public function getFormValues() : array
    {
        return $this->getFormInstance()->getValues();
    }

    /**
     * @return HTML_QuickForm2_Node[]
     * @throws Application_Formable_Exception
     */
    public function getErroneousElements() : array
    {
        return $this->getFormInstance()->getErroneousElements();
    }

    public function renderErrorMessages() : string
    {
        return $this->getFormInstance()->renderErrorMessages();
    }
    
    public function isFormSubmitted() : bool
    {
        return $this->getFormInstance()->isSubmitted();
    }

    /**
     * @param array<string,mixed> $formValues
     * @return $this
     * @throws Application_Formable_Exception
     */
    public function makeSubmitted(array $formValues=array()) : self
    {
        $this->getFormInstance()->makeSubmitted($formValues);

        return $this;
    }
    
   /**
    * Checks if the form has been submitted and is valid.
    * @return boolean
    */
    public function isFormValid() : bool
    {
        $form = $this->getFormInstance();

        if(!$form->isSubmitted()) {
            return false;
        }
        
        return $form->validate();
    }
    
   /**
    * Adds all page navigation variables as hidden variables to
    * the form.
    * 
    * @return $this
    */
    public function addFormablePageVars() : self
    {
        $this->requireFormableInitialized();
        
        $request = Application_Request::getInstance();
        $vars = Application_Admin_Skeleton::getPageParamNames();

        foreach($vars as $var) {
            $value = $request->getParam($var);
            if(!empty($value)) {
                $this->addHiddenVar($var, $value);
            }
        }
        
        return $this;
    }
    
   /**
    * Retrieves a form element by its name.
    * 
    * @param string $name
    * @return HTML_QuickForm2_Element|NULL
    */
    public function getElementByName(string $name) : ?HTML_QuickForm2_Element
    {
        return $this->getFormInstance()->getElementByName($name);
    }

    public function requireElementByName(string $name) : HTML_QuickForm2_Element
    {
        $el = $this->getElementByName($name);

        if($el !== null)
        {
            return $el;
        }

        throw new Application_Formable_Exception(
            'Form element not found.',
            sprintf(
                'Form element with name [%s] could not be found in the formable.',
                $name
            ),
            self::ERROR_ELEMENT_NOT_FOUND_BY_NAME
        );
    }

    /**
     * Retrieves the {@see UI_Form} instance that the formable uses in the background.
     *
     * @return UI_Form
     * @throws Application_Formable_Exception See {@see self::ERROR_FORMABLE_NOT_INITIALIZED}
     */
    public function getFormInstance() : UI_Form
    {
        $this->requireFormableInitialized();
        
        return $this->formableForm;
    }

    /**
     * Retrieves the UI instance used by the formable form.
     *
     * @return UI
     * @throws UI_Exception
     */
    public function getUI() : UI
    {
        if(isset($this->page))
        {
            return $this->page->getUI();
        }

        return UI::getInstance();
    }

    public function getPage() : UI_Page
    {
        return $this->requirePage();
    }

    protected function requirePage() : UI_Page
    {
        if(isset($this->page))
        {
            return $this->page;
        }

        throw new UI_Exception(
            'No page instance is available at this time.',
            '',
            self::ERROR_NO_PAGE_INSTANCE
        );
    }

    public function getTheme() : UI_Themes_Theme
    {
        return $this->getPage()->getTheme();
    }

    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this->getPage()->getRenderer();
    }

    public function display() : void
    {
        echo $this->render();
    }

    public function __toString()
    {
        return $this->render();
    }

    public function getUser() : Application_User
    {
        return Application::getUser();
    }

    public function getFormableContainer() : HTML_QuickForm2_Container
    {
        $this->requireFormableInitialized();

        if(isset($this->formableContainer)) {
            return $this->formableContainer;
        }

        return $this->getFormInstance()->getForm();
    }
    
    public function getFormableJSSubmit(bool $simulate_only=false) : string
    {
        return sprintf(
            "FormHelper.submit('%s', %s)",
            $this->getFormableName(),
            ConvertHelper::bool2string($simulate_only)
        );
    }

    /**
     * Retrieves the name of the form.
     * @return string
     * @throws Application_Formable_Exception
     */
    public function getFormableName() : string
    {
        if($this->isInitialized()) {
            return $this->getFormInstance()->getName();
        }
        
        return 'unset';
    }

    // region: C - Transforms, appends, etc.

    public function makeRequired(HTML_QuickForm2_Node $element, $message=null) : self
    {
        $this->getFormInstance()->makeRequired($element, $message);
        return $this;
    }

    public function setElementUnits(HTML_QuickForm2_Element $element, $units) : self
    {
        $this->getFormInstance()->setElementUnits($element, $units);
        return $this;
    }

    /**
     * Marks the specified element as structural, which means the
     * related object being modified will need a new draft revision
     * (only relevant with revisionables).
     *
     * @param HTML_QuickForm2_Element $element
     * @param bool $structural
     * @return $this
     */
    public function makeStructural(HTML_QuickForm2_Element $element, bool $structural=true) : self
    {
        $this->getFormInstance()->makeStructural($element, $structural);
        return $this;
    }

    /**
     * Adds a redactor to the specified element.
     * @param HTML_QuickForm2_Element $element
     * @return UI_MarkupEditor_Redactor
     */
    public function makeRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_Redactor
    {
        $this->requireFormableInitialized();

        return $this->formableForm->makeRedactor($element, $country);
    }

    /**
     * Makes the target element required by adding the standard
     * required rule with the default text, or the one specified
     * if set.
     *
     * @param HTML_QuickForm2_Element $element
     * @return $this
     */
    public function makeStandalone(HTML_QuickForm2_Element $element) : self
    {
        $this->getFormInstance()->makeStandalone($element);
        return $this;
    }

    /**
     * Hides the element from the readonly ("frozen") version of the form.
     * @param HTML_QuickForm2_Element $element
     * @return $this
     */
    public function makeHiddenWhenReadonly(HTML_QuickForm2_Element $element) : self
    {
        $this->getFormInstance()->makeHiddenWhenReadonly($element);
        return $this;
    }

    /**
     * Adds a validation rule to the element to limit the length
     * to the specified number of characters. Automatically adds
     * a validation hint for the length as well.
     *
     * Note: To limit to a specific length, set the min and max
     * to the same value.
     *
     * @param HTML_QuickForm2_Node $el
     * @param int $min
     * @param int $max
     * @return Application_Formable
     */
    public function makeLengthLimited(HTML_QuickForm2_Node $el, $min, $max)
    {
        $this->requireFormableInitialized();

        $this->formableForm->makeLengthLimited($el, $min, $max);
        return $this;
    }

    /**
     * Adds a validation rule to a number form element to limit the
     * value to the specified range.
     *
     * @param HTML_QuickForm2_Node $el
     * @param int|NULL $min
     * @param int|NULL $max
     * @return Application_Formable
     */
    public function makeMinMax(HTML_QuickForm2_Node $el, ?int $min=null, ?int $max=null)
    {
        $this->requireFormableInitialized();

        $this->formableForm->makeMinMax($el, $min, $max);
        return $this;
    }

    /**
     * Adds a string to prepend to an element. For example
     * for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $prependString
     * @return $this
     */
    public function setElementPrepend(HTML_QuickForm2_Element $element, $prependString) : self
    {
        $this->requireFormableInitialized();

        $this->formableForm->setElementPrepend($element, $prependString);
        return $this;
    }

    /**
     * Adds a string to append to an element. For example
     * for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $appendString
     * @return $this
     */
    public function setElementAppend(HTML_QuickForm2_Element $element, $appendString) : self
    {
        $this->requireFormableInitialized();

        $this->formableForm->setElementAppend($element, $appendString);
        return $this;
    }

    /**
     * Adds a callback function that will be called when the element is
     * rendered, to be able to influence how the element is rendered.
     *
     * @param HTML_QuickForm2_Node $element
     * @param callable $callback
     * @throws Application_Exception
     */
    public function addElementRenderCallback(HTML_QuickForm2_Node $element, $callback) : void
    {
        $this->formableForm->addRenderCallback($element, $callback);
    }

   /**
    * Appends a button to the element.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param UI_Button|UI_Bootstrap $button
    * @param boolean $whenFrozen Wether to append even when the element is frozen.
    * @return HTML_QuickForm2_Element
    */
    public function appendElementButton(HTML_QuickForm2_Element $element, $button, $whenFrozen=false)
    {
        return $this->formableForm->appendElementButton($element, $button, $whenFrozen);
    }
    
   /**
    * Appends HTML to the element, visually connected to the element.
    * @param HTML_QuickForm2_Element $element
    * @param string $html
    * @param bool $whenFrozen Wether to append even when the element is frozen.
    * @return HTML_QuickForm2_Element
    */
    public function appendElementHTML(HTML_QuickForm2_Element $element, $html, $whenFrozen=false)
    {
        return $this->formableForm->appendElementHTML($element, $html, $whenFrozen);
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
        return $this->formableForm->appendGenerateAliasButton($aliasElement, $fromElement);
    }

    // endregion

   /**
    * Enables the clientside form elements registry for
    * the form. This makes it possible to use 
    * <code>FormHelper.getRegistry('form_name')</code> to
    * get the registry for this form.
    * 
    * @param bool $enabled
    * @return $this
    */
    public function enableFormableClientRegistry(bool $enabled=true) : self
    {
        $this->getFormInstance()->enableClientRegistry($enabled);
        return $this;
    }
    
    public function setDefaultFormValues(array $values) : self
    {
        $this->getFormInstance()->setDefaultValues($values);
        return $this;
    }
    
   /**
    * @var Application_Formable_Container[]
    */
    protected array $containers = array();
    
    public function registerContainer(Application_Formable_Container $container)
    {
        $this->containers[] = $container;
        
        // if this formable has already been initialized,
        // tell the container to initialize itself as well
        if($this->formableInitialized) {
            $container->handleFormableInitialized();
        }
    }
    
    public function removeContainer(Application_Formable_Container $container)
    {
        $keep = array();
        foreach($this->containers as $existing) {
            if($existing !== $container) {
                $keep[] = $existing;
            }
        }
        
        $this->containers = $keep;
    }
    
    public function logFormable($message)
    {
        if(!$this->formableLogging) {
            return;
        }
        
        Application::log(sprintf(
            '%s | %s',
            $this->getFormableIdentification(),
            $message
        ));
    }
    
    public function getFormableIdentification()
    {
        $type = 'Formable';
        if($this instanceof Application_Formable_Container) {
            $type = 'Formable container';
        }
        
        if($this->isInitialized()) 
        {
            return sprintf(
                '%s [%s] | Name [%s] | JSID [%s]',
                $type,
                $this->getFormableInstanceID(),
                $this->getFormableName(),
                $this->getFormableJSID()
            );
        }

        return sprintf(
            '%s [%s] | Uninitialized',
            $type,
            $this->getFormableInstanceID()
        );
        
    }
    
    public function isInitialized()
    {
        return $this->formableInitialized;
    }
    
    /**
     * @return $this
     * @throws Application_Formable_Exception
     */
    public function makeReadonly() : self
    {
        $this->requireFormableInitialized();
        
        $this->formableForm->makeReadonly();
        
        return $this;
    }

    /**
     * Requires the form to be submitted and valid. Throws
     * an exception otherwise.
     *
     * @return $this
     *
     * @throws Application_Formable_Exception
     * @see Application_Formable::ERROR_FORM_NOT_VALID
     */
    public function requireFormValid() : self
    {
        if($this->isFormValid())
        {
            return $this;
        }

        throw new Application_Formable_Exception(
            'The form has not been submitted or is not valid.',
            '',
            self::ERROR_FORM_NOT_VALID
        );
    }
}
