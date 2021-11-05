<?php

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
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Application_Interfaces_Formable
{
    /**
     * Creates a serverside form and initializes the formable
     * with the form object. This is a shorthand for doing both
     * of these operations manually using {@link createForm()}
     * and {@link initFormable()}.
     *
     * @param string $name
     * @param array $defaultData
     */
    public function createFormableForm(string $name, array $defaultData = array());

    public function getFormableJSID(): string;

    public function getFormableInstanceID();

    public function getUser() : Application_User;

    /**
     * Sets the name of the default element of the form. Adds
     * a data attrribute to the form that can be used clientside
     * to determine the default element.
     *
     * @param string|HTML_QuickForm2_Element $elementNameOrObject
     * @return Application_Formable
     */
    public function setDefaultElement($elementNameOrObject);

    /**
     * Helper method to add a form element, which automatically sets
     * the correct ID for elements. If no container is specified, the
     * form itself is used as container.
     *
     * @param string $type
     * @param string $name
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element
     */
    public function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element;

    /**
     * Adds a text element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementText(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds an element for entering a hexadecimal color code.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementHexColor(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a textarea element.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Textarea
     */
    public function addElementTextarea(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Textarea;

    /**
     * Adds a custom HTML container element that will be output
     * at the element's position in the form.
     *
     * @param string $html
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementHTML(string $html, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds an abstract text to the form.
     *
     * @param string $abstract
     * @param string[] $classes
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementAbstract(string $abstract, array $classes = array()): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a configurable header element. Is intended to
     * replace the {@link addElementHeader()} method, which
     * is a bit too unwieldy.
     *
     * Returns an instance of the header helper, which can
     * be configured as you need. Call the <code>apply()</code>
     * method last to have the form elements added.
     *
     * @param string $label
     * @return Application_Formable_Header
     */
    public function addElementHeaderII(string $label): Application_Formable_Header;

    /**
     * Adds a file upload element.
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputFile
     */
    public function addElementFile(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputFile;

    /**
     * Adds a plupload-powered image uploader element that uses the application
     * media management classes to handle the uploaded images.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_ImageUploader
     */
    public function addElementImageUploader(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_ImageUploader;

    /**
     * Adds a visual select element, that lets users select values by
     * clicking images additionally to selecting from a dropdown.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_VisualSelect
     */
    public function addElementVisualSelect(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_VisualSelect;

    /**
     * Adds a select element. Use the element's API to add the
     * available options after adding it.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Select
     */
    public function addElementSelect(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Select;

    /**
     * Adds an element with static HTML content. Not to mistake with
     * the HTML element, which works the same but has no label.
     *
     * @param string $label
     * @param string $content
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementStatic(string $label, string $content, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a multiselect select element with search capabilities.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Multiselect
     */
    public function addElementMultiselect(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Multiselect;

    /**
     * Adds an element to the form that will be rendered as a heading.
     *
     * NOTE: You may prefer using the {@link addElementHeaderII} method
     * instead, which is easier to use.
     *
     * @param string $title
     * @param HTML_QuickForm2_Container|NULL $container
     * @param string $anchor The name of the anchor that can be used to jump to the heading
     * @param boolean $collapsed Whether the header should start collapsed
     * @return HTML_QuickForm2_Element_InputText
     * @see Application_Formable::addElementHeaderII()
     */
    public function addElementHeader($title, $container = null, $anchor = null, $collapsed = true);

    /**
     * Adds a group to contain elements, but which does not generate any layout.
     * Use this if you have to namespace element names in case there are duplicate
     * element names.
     *
     * @param string $name
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Container_Group
     */
    public function addElementGroupLayoutless(string $name, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Container_Group;

    /**
     * Adds a hidden variable field to the form.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHiddenVar(string $name, string $value = '');

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
    public function addHiddenVars(array $vars);

    /**
     * Makes the target element required by adding the standard
     * required rule with the default text, or the one specified
     * if set.
     *
     * @param HTML_QuickForm2_Node $element
     * @param string $message The error message to display
     * @return Application_Formable
     */
    public function makeRequired(HTML_QuickForm2_Node $element, $message = null);

    /**
     * @return string
     */
    public function renderFormable();

    public function addRulePhone(HTML_QuickForm2_Element $element);

    public function addRuleEmail(HTML_QuickForm2_Element $element);

    public function addRuleAlias(HTML_QuickForm2_Element $element, $allowCapitalLetters = false);

    /**
     * Adds a callback rule. Helper method for easier access to
     * the QuickForm API for this. The first argument of the callback
     * is always the value to validate, and the last is the
     * rule object instance, even if custom arguments are specified.
     *
     * @param HTML_QuickForm2_Element $element
     * @param callable $callback
     * @param string $errorMessage
     * @param mixed[] $arguments Arguments for the callback
     * @return HTML_QuickForm2_Rule_Callback
     */
    public function addRuleCallback(HTML_QuickForm2_Element $element, $callback, string $errorMessage, array $arguments = array()) : HTML_QuickForm2_Rule_Callback;

    public function addRuleLabel(HTML_QuickForm2_Element $element);

    /**
     * Adds a rule to enter a filename.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleFilename(HTML_QuickForm2_Element $element);

    /**
     * Adds a rule to validate as name or title, which is less
     * restrictive than the label rule.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleNameOrTitle(HTML_QuickForm2_Element $element);

    /**
     * Adds a validation rule to the element that disallows
     * using HTML in its content.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleNoHTML(HTML_QuickForm2_Element $element): HTML_QuickForm2_Element;

    public function addRuleRegex(HTML_QuickForm2_Element $element, string $regex, string $message);

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
    public function addRuleInteger(HTML_QuickForm2_Element $element, int $min = 0, int $max = 0);

    /**
     * Adds a date time validation to the element.
     *
     * @param HTML_QuickForm2_Element $element
     * @return HTML_QuickForm2_Element
     */
    public function addRuleISODate(HTML_QuickForm2_Element $element);

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
    public function addRuleRequiredIfOther(HTML_QuickForm2_Node $element, $message, HTML_QuickForm2_Node $otherElement, $otherValue, $operator = '==');

    /**
     * Adds a rule to make the element required if the
     * other element's value is not empty.
     *
     * @param HTML_QuickForm2_Node $element
     * @param HTML_QuickForm2_Node $otherElement
     * @param string|NULL $message A specific message, to overwrite the default message
     */
    public function addRuleRequiredIfOtherNonEmpty(HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $otherElement, $message = null);

    public function addRuleFloat(HTML_QuickForm2_Element $element);

    /**
     * Adds a switch element to the form and returns its instance.
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Switch
     */
    public function addElementSwitch(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Switch;

    /**
     * Hides the element from the readonly ("frozen") version of the form.
     * @param HTML_QuickForm2_Element $element
     * @return $this
     */
    public function makeHiddenWhenReadonly(HTML_QuickForm2_Element $element);

    /**
     * Creates an integer form element that comes with a validation
     * rule for integer values.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementInteger(string $name, string $label, ?HTML_QuickForm2_Container $container = null, int $min = 0, int $max = 0): HTML_QuickForm2_Element_InputText;

    /**
     * Creates an form element to enter a date with time.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementISODate(string $name, string $label, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Creates a form element to enter a percentage value.
     *
     * @param string $name
     * @param string $label
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_InputText
     */
    public function addElementPercent(string $name, string $label, ?HTML_QuickForm2_Container $container = null, float $min = 0, float $max = 100) : HTML_QuickForm2_Element_InputText;

    /**
     * Sets the label for the units this element should be entered in,
     * e.g. "Centimetres". Will be displayed next to the element as type
     * hint for the user.
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $units
     * @return Application_Formable
     */
    public function setElementUnits(HTML_QuickForm2_Element $element, $units);

    /**
     * Marks the specified element as structural, which means the
     * related object being modified will need a new draft revision
     * (only relevant with revisionables).
     *
     * @param HTML_QuickForm2_Element $element
     * @param bool $structural
     * @return Application_Formable
     */
    public function makeStructural(HTML_QuickForm2_Element $element, bool $structural = true);

    /**
     * Adds a redactor to the specified element.
     * @param HTML_QuickForm2_Element $element
     * @return UI_MarkupEditor_Redactor
     */
    public function makeRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country): UI_MarkupEditor_Redactor;

    /**
     * Makes the target element required by adding the standard
     * required rule with the default text, or the one specified
     * if set.
     *
     * @param HTML_QuickForm2_Element $element
     * @return $this
     */
    public function makeStandalone(HTML_QuickForm2_Element $element);

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
     * @return Application_Formable
     */
    public function makeLengthLimited(HTML_QuickForm2_Node $el, $min, $max);

    /**
     * Adds a validation rule to a number form element to limit the
     * value to the specified range.
     *
     * @param HTML_QuickForm2_Node $el
     * @param number $min
     * @param number $max
     * @return Application_Formable
     */
    public function makeMinMax(HTML_QuickForm2_Node $el, $min = null, $max = null);

    /**
     * Adds a string to prepend to an element. For example
     * for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $prependString
     * @return Application_Formable
     */
    public function setElementPrepend(HTML_QuickForm2_Element $element, $prependString);

    /**
     * Adds a string to append to an element. For example
     * for units, like "Centimetres".
     *
     * @param HTML_QuickForm2_Element $element
     * @param string $appendString
     * @return Application_Formable
     */
    public function setElementAppend(HTML_QuickForm2_Element $element, $appendString);

    /**
     * Retrieves the form's values as an associative array
     * with element name => value pairs.
     *
     * @return array<string,mixed>
     */
    public function getFormValues();

    public function isFormSubmitted(): bool;

    /**
     * Checks if the form has been submitted and is valid.
     * @return boolean
     */
    public function isFormValid() : bool;

    /**
     * Adds all page navigation variables as hidden variables to
     * the form.
     *
     * @return Application_Formable
     */
    public function addFormablePageVars();

    /**
     * Retrieves a form element by its name.
     *
     * @param string $name
     * @return HTML_QuickForm2_Element|NULL
     */
    public function getElementByName(string $name): ?HTML_QuickForm2_Element;

    /**
     * @param string $name
     * @return HTML_QuickForm2_Element
     * @throws Application_Formable_Exception
     */
    public function requireElementByName(string $name): HTML_QuickForm2_Element;

    /**
     * Retrieves the formable's UI Form instance.
     * @return UI_Form
     */
    public function getFormInstance(): UI_Form;

    /**
     * Retrieves the UI instance used by the formable form.
     * @return UI
     */
    public function getUI();

    /**
     * Retrieves the form container to which elements are
     * currently added.
     *
     * @return HTML_QuickForm2_Container
     */
    public function getFormableContainer();

    /**
     * Retrieves the javascript statement required to submit the form,
     * @param bool $simulate_only
     * @return string
     */
    public function getFormableJSSubmit(bool $simulate_only = false);

    /**
     * Retrieves the name of the form.
     * @return string
     */
    public function getFormableName();

    /**
     * Appends a button to the element.
     *
     * @param HTML_QuickForm2_Element $element
     * @param UI_Button|UI_Bootstrap $button
     * @param boolean $whenFrozen Wether to append even when the element is frozen.
     * @return HTML_QuickForm2_Element
     */
    public function appendElementButton(HTML_QuickForm2_Element $element, $button, $whenFrozen = false);

    /**
     * Appends HTML to the element, visually connected to the element.
     * @param HTML_QuickForm2_Element $element
     * @param string $html
     * @param bool $whenFrozen Wether to append even when the element is frozen.
     * @return HTML_QuickForm2_Element
     */
    public function appendElementHTML(HTML_QuickForm2_Element $element, $html, $whenFrozen = false);

    /**
     * Appends a button to the element to generate an alias from the content
     * of the target element. Uses the AJAX transliterate function to create
     * the alias from a string.
     *
     * @param HTML_QuickForm2_Element $aliasElement
     * @param HTML_QuickForm2_Element $fromElement
     * @return HTML_QuickForm2_Element
     */
    public function appendGenerateAliasButton(HTML_QuickForm2_Element $aliasElement, HTML_QuickForm2_Element $fromElement);

    /**
     * Enables the clientside form elements registry for
     * the form. This makes it possible to use
     * <code>FormHelper.getRegistry('form_name')</code> to
     * get the registry for this form.
     *
     * @param bool $enabled
     * @return Application_Formable
     */
    public function enableFormableClientRegistry(bool $enabled = true);

    public function setDefaultFormValues($values);

    public function registerContainer(Application_Formable_Container $container);

    public function removeContainer(Application_Formable_Container $container);

    public function logFormable($message);

    public function getFormableIdentification();

    public function isInitialized();

    /**
     * Adds a callback function that will be called when the element is
     * rendered, to be able to influence how the element is rendered.
     *
     * @param HTML_QuickForm2_Node $element
     * @param callable $callback
     * @throws Application_Exception
     */
    public function addElementRenderCallback(HTML_QuickForm2_Node $element, $callback): void;

    public function makeReadonly(): Application_Formable;
}