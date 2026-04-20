# UI Form - Core Architecture
_SOURCE: Public class signatures for the form, elements, renderers, validators, and rules_
# Public class signatures for the form, elements, renderers, validators, and rules
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Form.php
            └── Form/
                └── Element/
                    ├── DateTimePicker/
                    │   ├── BasicTime.php
                    ├── Datepicker.php
                    ├── ExpandableSelect.php
                    ├── HTMLDatePicker.php
                    ├── HTMLDateTimePicker.php
                    ├── HTMLTimePicker.php
                    ├── ImageUploader.php
                    ├── Multiselect.php
                    ├── Switch.php
                    ├── TreeSelect.php
                    ├── UIButton.php
                    ├── VisualSelect.php
                    ├── VisualSelect/
                    │   └── ImageSet.php
                    │   └── Optgroup.php
                    │   └── VisualSelectOption.php
                └── FormException.php
                └── Renderer.php
                └── Renderer/
                    ├── CommentGenerator.php
                    ├── CommentGenerator/
                    │   ├── DataType.php
                    │   ├── DataType/
                    │   │   └── Date.php
                    │   │   └── Float.php
                    │   │   └── ISODate.php
                    │   │   └── Integer.php
                    │   │   └── RegexHint.php
                    ├── Element.php
                    ├── ElementCallback.php
                    ├── ElementFilter.php
                    ├── ElementFilter/
                    │   ├── RenderDef.php
                    ├── Registry.php
                    ├── RenderType.php
                    ├── RenderType/
                    │   ├── Button.php
                    │   ├── Default.php
                    │   ├── Group.php
                    │   ├── Header.php
                    │   ├── Hint.php
                    │   ├── Html.php
                    │   ├── LayoutlessGroup.php
                    │   ├── Paragraph.php
                    │   ├── Radio.php
                    │   ├── SelfRenderingGroup.php
                    │   ├── Static.php
                    │   ├── Subheader.php
                    │   ├── Tab.php
                    ├── Sections.php
                    ├── Sections/
                    │   ├── Section.php
                    ├── Tabs.php
                    ├── Tabs/
                    │   └── Tab.php
                └── Rule/
                    ├── Equals.php
                └── Validator.php
                └── Validator/
                    └── Date.php
                    └── Float.php
                    └── ISODate.php
                    └── Integer.php
                    └── Percent.php

```
###  Path: `/src/classes/UI/Form.php`

```php
namespace ;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\FileHelper as FileHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\JSHelper as JSHelper;
use AppUtils\RegexHelper as RegexHelper;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use HTML\QuickForm2\DataSource\ManualSubmitDataSource as ManualSubmitDataSource;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;
use UI\Form\FormException as FormException;

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
	public const ERROR_COULD_NOT_SUBMIT_FORM = 45524017;
	public const ERROR_ELEMENT_NOT_FOUND = 45524018;

	/**
	 * Stores the string that form element IDs get prefixed with.
	 * @var string
	 */
	public const ID_PREFIX = 'f-';
	public const ATTRIBUTE_LABEL_ID = 'data-label-id';
	public const REL_BUTTON = 'Button';
	public const REL_LAYOUT_LESS_GROUP = 'LayoutlessGroup';
	public const FORM_PREFIX = 'form-';
	public const ELEMENT_TYPE_DATE_PICKER = 'datepicker';

	public function getID(): string
	{
		/* ... */
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
	public static function setElementLabelID(HTML_QuickForm2_Node $node, string $id): void
	{
		/* ... */
	}


	public function getJSID(): string
	{
		/* ... */
	}


	public function callback_onNodeAdded(HTML_QuickForm2_Event_NodeAdded $event): void
	{
		/* ... */
	}


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
	public function registerCustomRule(string $alias, string $ruleName): void
	{
		/* ... */
	}


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
	public function registerCustomElement(string $alias, string $elementName): void
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all registered custom elements.
	 * @return array Indexed array with these keys in each entry: "alias", "name" and "file"
	 */
	public function getCustomElements(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the default data source of the form, which
	 * is used to set the default values of elements.
	 *
	 * @return HTML_QuickForm2_DataSource_Array
	 */
	public function getDefaultDataSource(): HTML_QuickForm2_DataSource_Array
	{
		/* ... */
	}


	/**
	 * @param array<string,mixed> $values
	 * @return $this
	 */
	public function setDefaultValues(array $values): self
	{
		/* ... */
	}


	/**
	 * Selects the default element in the form. If possible, when the page is
	 *  loaded, the field will automatically get focus.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return $this
	 */
	public function setDefaultElement(HTML_QuickForm2_Node $element): self
	{
		/* ... */
	}


	/**
	 * Sets an attribute of the form element itself.
	 *
	 * @param string $name
	 * @param string|int|float|NULL $value
	 * @return UI_Form
	 */
	public function setAttribute(string $name, $value): self
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2_Node[]
	 */
	public function getErroneousElements(array $result = []): array
	{
		/* ... */
	}


	public function renderErrorMessages(): string
	{
		/* ... */
	}


	/**
	 * Manually submits the form given the specified data.
	 *
	 * @param array<string,mixed> $formValues
	 * @return $this
	 * @throws Application_Formable_Exception
	 */
	public function makeSubmitted(array $formValues = []): self
	{
		/* ... */
	}


	public function addGroupLayoutless(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Container_Group
	{
		/* ... */
	}


	/**
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 * @see UI_Form_Renderer_CommentGenerator::addMarkdownComment()
	 */
	public function addMarkdownSupport(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function renderJSSelectFilterable(string $selector): string
	{
		/* ... */
	}


	/**
	 * Adds a class to the form tag itself.
	 *
	 * @param string $className
	 * @return UI_Form
	 */
	public function addClass(string $className): self
	{
		/* ... */
	}


	public function removeClass(string $className): self
	{
		/* ... */
	}


	/**
	 * Returns an element if its id is found
	 *
	 * @param string $id Element id to search for
	 * @return HTML_QuickForm2_Node|null
	 */
	public function getElementByID(string $id): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Retrieves the first element in the form whose name
	 * matches the specified name.
	 *
	 * @param string $name
	 * @return HTML_QuickForm2_Node|null
	 * @throws BaseClassHelperException
	 */
	public function getElementByName(string $name): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function requireElementByName(string $name): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function getValue($elementID)
	{
		/* ... */
	}


	/**
	 * Checks whether the form has been submitted.
	 * @return boolean
	 */
	public function isSubmitted(): bool
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2
	 */
	public function getForm(): HTML_QuickForm2
	{
		/* ... */
	}


	/**
	 * Retrieves all required elements in the form, or the
	 * specified container if the first parameter is set.
	 *
	 * @param HTML_QuickForm2_Container|NULL $container
	 * @param array $result
	 * @return HTML_QuickForm2_Node[]
	 */
	public function getRequiredElements(?HTML_QuickForm2_Container $container = null, array $result = []): array
	{
		/* ... */
	}


	/**
	 * In silent validation mode, validation errors are not
	 * displayed to the user, and the form does not add any
	 * error messages to the UI.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setSilentValidation(bool $enabled = true): self
	{
		/* ... */
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
	public function simulateSubmit(): self
	{
		/* ... */
	}


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
	public function validate(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all form element instances that have errors
	 * after validation, as an indexed array with form element
	 * instances.
	 *
	 * @return HTML_QuickForm2_Node[]
	 * @throws UI_Exception
	 */
	public function getInvalidElements(bool $simulateSubmit = false): array
	{
		/* ... */
	}


	/**
	 * Checks whether the form's submitted data is valid. If it
	 * has not been validated yet, it is validated automatically.
	 *
	 * @return boolean
	 * @throws UI_Exception
	 */
	public function isValid(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves all form element values in an associative array.
	 *
	 * @param boolean $removeTrackingVar
	 * @return array<string,mixed>
	 */
	public function getValues(bool $removeTrackingVar = false): array
	{
		/* ... */
	}


	/**
	 * @return HTML_QuickForm2_Element_ImageUploader[]
	 */
	public function getImageUploaderElements(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the request variable that is used by the
	 * form to track whether it has been submitted.
	 *
	 * @return string
	 */
	public function getTrackingName(): string
	{
		/* ... */
	}


	public function isTrackingElement(HTML_QuickForm2_Node $element): bool
	{
		/* ... */
	}


	public function isDummyElement(HTML_QuickForm2_Node $element): bool
	{
		/* ... */
	}


	public function renderHorizontal(): string
	{
		/* ... */
	}


	public function renderColumnized(): string
	{
		/* ... */
	}


	/**
	 * Makes the form readonly so that it only shows element values,
	 * without editing capabilities.
	 *
	 * @param bool $readonly
	 * @return $this
	 */
	public function makeReadonly(bool $readonly = true): self
	{
		/* ... */
	}


	public function isReadonly(): bool
	{
		/* ... */
	}


	/**
	 * Makes the field labels wider to allow for longer labels.
	 * @return $this
	 */
	public function makeLabelsWider(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Turns the form into a more compact form layout.
	 * @return $this
	 */
	public function makeCondensed(bool $enabled = true): self
	{
		/* ... */
	}


	/**
	 * Marks the form as being collapsible: all headers within the
	 * form will be rendered so that their contained form elements
	 * can be collapsed/expanded at will.
	 *
	 * @return $this
	 */
	public function makeCollapsible(bool $enabled = true): self
	{
		/* ... */
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
	public static function getRegexHint(string $name): string
	{
		/* ... */
	}


	/**
	 * Replaces commas with dots in a number, and removes spaces.
	 * @param string|NULL $value
	 * @return string
	 */
	public function filter_adjustNumericNotation(?string $value): string
	{
		/* ... */
	}


	/**
	 * Retrieves the first element in the container's element collection,
	 * or null if it does not have any elements.
	 *
	 * @param HTML_QuickForm2_Container $container
	 * @return NULL|HTML_QuickForm2_Node
	 */
	public function getFirstElement(HTML_QuickForm2_Container $container): ?HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function handle_validateMinMax($value, ?int $min, ?int $max): bool
	{
		/* ... */
	}


	/**
	 * Sets the onsubmit attribute of the form tag to the specified
	 * javascript statement string.
	 *
	 * @param string $statement
	 * @return $this
	 */
	public function onSubmit(string $statement): self
	{
		/* ... */
	}


	/**
	 * Creates an ID for a form element following the naming scheme standard
	 * so that clientside scripts can access them easily as well.
	 *
	 * @param string $jsid
	 * @param string|NULL $elementName
	 * @return string
	 * @throws FormException
	 */
	public function createElementID(string $jsid, ?string $elementName): string
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * regular item labels.
	 *
	 * @param string|NULL $label
	 * @return boolean
	 */
	public static function validateLabel(?string $label): bool
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * item aliases.
	 *
	 * @param string|NULL $alias
	 * @return boolean
	 */
	public static function validateAlias(?string $alias): bool
	{
		/* ... */
	}


	/**
	 * Validates the specified string with the regex for
	 * email addresses.
	 *
	 * @param string|NULL $email
	 * @return boolean
	 */
	public static function validateEmail(?string $email): bool
	{
		/* ... */
	}


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
	public function addEventHandler(string $name, callable $handler): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_InputText
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addText(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addTextarea(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Textarea
	{
		/* ... */
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
	public function addSubheader($header, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addHiddenVars(array $vars): self
	{
		/* ... */
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
	public function addPercent(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
		float $min = 0,
		float $max = 100,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addImageUploader(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_ImageUploader
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Element_ExpandableSelect
	 *
	 * @throws BaseClassHelperException
	 * @throws FormException
	 */
	public function addExpandableSelect(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_ExpandableSelect
	{
		/* ... */
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
	public function addFile(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputFile
	{
		/* ... */
	}


	public function addVisualSelect(
		string $name,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_VisualSelect
	{
		/* ... */
	}


	/**
	 * @param string $type
	 * @param string $name
	 * @param HTML_QuickForm2_Container|null $container
	 * @return HTML_QuickForm2_Node
	 * @throws FormException
	 */
	public function addElement(string $type, string $name, ?HTML_QuickForm2_Container $container): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addTab(string $name, $label, $description = null): HTML_QuickForm2_Container_Group
	{
		/* ... */
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
	public function addHeader(
		string $title,
		?HTML_QuickForm2_Container $container = null,
		?string $anchor = null,
		bool $collapsed = true,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addHexColor(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
	}


	public function addStatic(
		string $label,
		string $content,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addHTML($html, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addHint($text, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addISODate(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addInteger(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
		int $min = 0,
		int $max = 0,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addParagraph($text, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addHiddenVar(
		string $name,
		string|int|float|null $value = null,
		?string $id = null,
	): HTML_QuickForm2_Element_InputHidden
	{
		/* ... */
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
	public function addLinkButton($url, $label, $tooltip = ''): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
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
	public function addPrimarySubmit($label, string $name = 'save', $tooltip = ''): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
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
	public function addDevPrimarySubmit(string $label, string $name = 'save'): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
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
	public function addButton(string $name): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
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
	public function addSubmit($label, string $name = 'save', $tooltip = null): HTML_QuickForm2_Element_UIButton
	{
		/* ... */
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
	public function addSwitch(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Switch
	{
		/* ... */
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
	public function addTreeSelect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_TreeSelect
	{
		/* ... */
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
	public function addSelect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Select
	{
		/* ... */
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
	public function addMultiselect(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_Multiselect
	{
		/* ... */
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
	public function addAlias(
		?string $name = null,
		?string $label = null,
		?string $comment = null,
		bool $structural = true,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addAbstract(
		string $abstract,
		array $classes = [],
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_InputText
	{
		/* ... */
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
	public function addDatepicker(
		string $name,
		string $label,
		?HTML_QuickForm2_Container $container = null,
	): HTML_QuickForm2_Element_HTMLDateTimePicker
	{
		/* ... */
	}


	/**
	 * Adds an email validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleEmail(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addRuleURL(HTML_QuickForm2_Element $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a phone number validation rule to the element.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRulePhone(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleAlias(HTML_QuickForm2_Node $element, bool $allowCapitalLetters = false): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleCallback(
		HTML_QuickForm2_Node $element,
		callable $callback,
		string $errorMessage,
		$arguments = null,
	): HTML_QuickForm2_Rule_Callback
	{
		/* ... */
	}


	/**
	 * Adds a filename validation rule, which checks that the
	 * name has an extension and contains only valid characters.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleFilename(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleInteger(HTML_QuickForm2_Node $element, int $min = 0, int $max = 0): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleISODate(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleFloat(HTML_QuickForm2_Node $element, float $min = 0.0, float $max = 0.0): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addRulePercent(
		HTML_QuickForm2_Element $element,
		float $min = 0,
		float $max = 100,
	): HTML_QuickForm2_Element
	{
		/* ... */
	}


	/**
	 * Converts commas in the value to dots (used for numeric values)
	 * @param mixed $value
	 * @return string
	 */
	public function callback_convertComma($value): string
	{
		/* ... */
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
	public function addRuleDate(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function scrollToHeader(string $anchorName): self
	{
		/* ... */
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
	public function makeRedactor(
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor_Redactor
	{
		/* ... */
	}


	public function makeCKEditor(
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor_CKEditor
	{
		/* ... */
	}


	public function makeMarkupEditor(
		string $editorID,
		HTML_QuickForm2_Element $element,
		Application_Countries_Country $country,
	): UI_MarkupEditor
	{
		/* ... */
	}


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
	public function addRenderCallback(HTML_QuickForm2_Node $element, callable $callback): void
	{
		/* ... */
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
	public function makeStructural(HTML_QuickForm2_Node $el, bool $structural = true): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Sets the element to be rendered as a standalone element:
	 * this hides the label, and removes the element's indentation,
	 * so it can use the full available width.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 */
	public function makeStandalone(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function makeRequired(HTML_QuickForm2_Node $el, $message = null): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function makeLengthLimited(HTML_QuickForm2_Node $el, ?int $min, ?int $max): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function makeMinMax(HTML_QuickForm2_Node $el, ?int $min = null, ?int $max = null): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Hides this element from the frozen variant of the form.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 */
	public function makeHiddenWhenReadonly(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function setElementUnits(HTML_QuickForm2_Node $element, $units): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function addFilterComma2Dot(HTML_QuickForm2_Element $element): HTML_QuickForm2_Element
	{
		/* ... */
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
	public function setElementAppend(HTML_QuickForm2_Node $element, $appendString): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function setElementPrepend(HTML_QuickForm2_Node $element, $prependString): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function appendElementHTML(
		HTML_QuickForm2_Node $element,
		$html,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Appends a button after the element's input.
	 *
	 * @param HTML_QuickForm2_Node $element
	 * @param UI_Button|UI_Bootstrap $button
	 * @param boolean $whenFrozen Whether to display this even when the element is frozen.
	 * @return HTML_QuickForm2_Node
	 */
	public function appendElementButton(
		HTML_QuickForm2_Node $element,
		$button,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function appendGenerateAliasButton(
		HTML_QuickForm2_Node $aliasElement,
		HTML_QuickForm2_Node $fromElement,
	): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function prependElementHTML(
		HTML_QuickForm2_Node $element,
		$html,
		bool $whenFrozen = false,
	): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a label validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleLabel(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a name or title validation rule to the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleNameOrTitle(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Adds a rule that disallows using HTML in the element.
	 * @param HTML_QuickForm2_Node $element
	 * @return HTML_QuickForm2_Node
	 *
	 * @throws HTML_QuickForm2_Exception
	 */
	public function addRuleNoHTML(HTML_QuickForm2_Node $element): HTML_QuickForm2_Node
	{
		/* ... */
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
	public function addRuleRegex(HTML_QuickForm2_Node $element, string $regex, string $message): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Parses a date string into a date object. The date must have
	 * a format matching the  {@see RegexHelper::REGEX_DATE} regular
	 * expression.
	 *
	 * @param string|NULL $dateString
	 * @return NULL|DateTime
	 */
	public static function parseDate(?string $dateString): ?DateTime
	{
		/* ... */
	}


	/**
	 * Sets the title of the form. This is typically used
	 * in the form rendering template as title for the content
	 * section in which the form is shown.
	 *
	 * @param string|number|StringableInterface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Sets the abstract of the form. This is typically used
	 * in the form rendering template as title for the content
	 * section in which the form is shown.
	 *
	 * @param string|number|StringableInterface|NULL $abstract
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setAbstract($abstract): self
	{
		/* ... */
	}


	public function getAbstract(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the matching UI_Form instance for the specified
	 * QuickForm element, or NULL if it could not be found.
	 *
	 * @param HTML_QuickForm2_Node $el
	 * @return UI_Form|NULL
	 * @throws FormException
	 */
	public static function getInstanceByElement(HTML_QuickForm2_Node $el): ?UI_Form
	{
		/* ... */
	}


	public function hasElements(bool $includeHiddens = false): bool
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
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
	public function getJSSubmitHandler(bool $simulate = false): string
	{
		/* ... */
	}


	/**
	 * @param class-string|UI_DataGrid|UI_Form|Application_Formable|Application_Interfaces_Formable|mixed $subject
	 * @return string
	 * @throws Application_Formable_Exception
	 */
	public static function resolveFormName($subject): string
	{
		/* ... */
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
	public static function renderJSSubmitHandler($subject, bool $simulate = false): string
	{
		/* ... */
	}


	/**
	 * Enables or disables the clientside form elements registry: this
	 * is an easy way to access information on the form on the client
	 * side, from sections to individual elements. By default, this is
	 * disabled.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function enableClientRegistry(bool $enabled = true): self
	{
		/* ... */
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
	public static function renderDummySubmit(string $id = ''): string
	{
		/* ... */
	}


	public function compileExamples(): string
	{
		/* ... */
	}


	public function compileValues(): string
	{
		/* ... */
	}


	public function resolveContainer(?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Container
	{
		/* ... */
	}


	public function getElementValidator(HTML_QuickForm2_Element $element): ?UI_Form_Validator
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/DateTimePicker/BasicTime.php`

```php
namespace Application\UI\Form\Element\DateTimePicker;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI_Exception as UI_Exception;

class BasicTime implements StringableInterface
{
	public const ERROR_INVALID_TIME = 145701;

	public function getAsString(): string
	{
		/* ... */
	}


	public function getHour(): ?int
	{
		/* ... */
	}


	public function getMinutes(): ?int
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/DateTimePicker/BasicTime.php`

```php
namespace Application\UI\Form\Element\DateTimePicker;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use UI_Exception as UI_Exception;

class BasicTime implements StringableInterface
{
	public const ERROR_INVALID_TIME = 145701;

	public function getAsString(): string
	{
		/* ... */
	}


	public function getHour(): ?int
	{
		/* ... */
	}


	public function getMinutes(): ?int
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Datepicker.php`

```php
namespace ;

use AppLocalize\Localization as Localization;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;

/**
 * Bootstrap-based datepicker element for selecting dates.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see http://eternicode.github.io/bootstrap-datepicker
 * @see https://github.com/eternicode/bootstrap-datepicker
 */
class HTML_QuickForm2_Element_Datepicker extends HTML_QuickForm2_Element_InputText
{
	public const REGEX_DATE = '%\A[0-9]{2}/[0-9]{2}/[0-9]{2}\z%m';

	public function __toString()
	{
		/* ... */
	}


	public function getPlaceholder()
	{
		/* ... */
	}


	public function getRegex(): string
	{
		/* ... */
	}


	/**
	 * Retrieves a date object for a value of the element,
	 * or null otherwise (if the value is empty, for ex.).
	 *
	 * @param string|NULL $value
	 * @return null|DateTime
	 */
	public function getDate(?string $value): ?DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Datepicker.php`

```php
namespace ;

use AppLocalize\Localization as Localization;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;

/**
 * Bootstrap-based datepicker element for selecting dates.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see http://eternicode.github.io/bootstrap-datepicker
 * @see https://github.com/eternicode/bootstrap-datepicker
 */
class HTML_QuickForm2_Element_Datepicker extends HTML_QuickForm2_Element_InputText
{
	public const REGEX_DATE = '%\A[0-9]{2}/[0-9]{2}/[0-9]{2}\z%m';

	public function __toString()
	{
		/* ... */
	}


	public function getPlaceholder()
	{
		/* ... */
	}


	public function getRegex(): string
	{
		/* ... */
	}


	/**
	 * Retrieves a date object for a value of the element,
	 * or null otherwise (if the value is empty, for ex.).
	 *
	 * @param string|NULL $value
	 * @return null|DateTime
	 */
	public function getDate(?string $value): ?DateTime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/ExpandableSelect.php`

```php
namespace ;

/**
 * Multiple selection select element with integrated controls
 * to select and deselect elements, as well as to expand or
 * collapse the select to show or hide elements.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ExpandableSelect extends HTML_QuickForm2_Element_Select
{
	/**
	 * Sets the maximum amount of elements to show in the
	 * select element.
	 *
	 * @param int $size
	 * @return $this
	 */
	public function setMaxSize(int $size): self
	{
		/* ... */
	}


	public function getMaxSize(): int
	{
		/* ... */
	}


	public function getSize(): int
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/ExpandableSelect.php`

```php
namespace ;

/**
 * Multiple selection select element with integrated controls
 * to select and deselect elements, as well as to expand or
 * collapse the select to show or hide elements.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ExpandableSelect extends HTML_QuickForm2_Element_Select
{
	/**
	 * Sets the maximum amount of elements to show in the
	 * select element.
	 *
	 * @param int $size
	 * @return $this
	 */
	public function setMaxSize(int $size): self
	{
		/* ... */
	}


	public function getMaxSize(): int
	{
		/* ... */
	}


	public function getSize(): int
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLDatePicker.php`

```php
namespace ;

/**
 * Element that is used to handle generate HTML input with type date.
 * All browsers (except IE) will open calendar as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDatePicker extends HTML_QuickForm2_Element_Input
{
	public const REGEX_GROUP_DATE = '([0-9]{4}-[0-9]{2}-[0-9]{2})';
	public const ERROR_INVALID_DATE_VALUE = 145801;

	public function getType(): string
	{
		/* ... */
	}


	public static function isValidDateString(string $date): bool
	{
		/* ... */
	}


	/**
	 * @return DateTime|null
	 * @throws UI_Exception {@see self::ERROR_INVALID_DATE_VALUE}
	 */
	public function getDate(): ?DateTime
	{
		/* ... */
	}


	public function getYear(): ?int
	{
		/* ... */
	}


	public function getMonth(): ?int
	{
		/* ... */
	}


	public function getDay(): ?int
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|NULL $value
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLDatePicker.php`

```php
namespace ;

/**
 * Element that is used to handle generate HTML input with type date.
 * All browsers (except IE) will open calendar as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDatePicker extends HTML_QuickForm2_Element_Input
{
	public const REGEX_GROUP_DATE = '([0-9]{4}-[0-9]{2}-[0-9]{2})';
	public const ERROR_INVALID_DATE_VALUE = 145801;

	public function getType(): string
	{
		/* ... */
	}


	public static function isValidDateString(string $date): bool
	{
		/* ... */
	}


	/**
	 * @return DateTime|null
	 * @throws UI_Exception {@see self::ERROR_INVALID_DATE_VALUE}
	 */
	public function getDate(): ?DateTime
	{
		/* ... */
	}


	public function getYear(): ?int
	{
		/* ... */
	}


	public function getMonth(): ?int
	{
		/* ... */
	}


	public function getDay(): ?int
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|NULL $value
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLDateTimePicker.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;

/**
 * EXPERIMENTAL! Element that is used to handle generate HTML input with type date and time together.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDateTimePicker extends HTML_QuickForm2_Container_Group
{
	public const ELEMENT_NAME_DATE = 'date';
	public const ELEMENT_NAME_TIME = 'time';
	public const CSS_FILE_NAME = 'forms/date-picker.css';

	public function getType(): string
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|array|NULL $value A date string in the format <code>Y-m-d H:i</code>,
	 *          a <code>DateTime</code> instance, or an array with keys
	 *          <code>date</code> and <code>time</code>.
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	public function getDateElement(): HTML_QuickForm2_Element_HTMLDatePicker
	{
		/* ... */
	}


	public function getTimeElement(): HTML_QuickForm2_Element_HTMLTimePicker
	{
		/* ... */
	}


	public static function parseDateTimeString(string $string, bool $timeOptional): ?array
	{
		/* ... */
	}


	public function getDate(): ?DateTime
	{
		/* ... */
	}


	public function getDateString(): ?string
	{
		/* ... */
	}


	public function getValue(): ?string
	{
		/* ... */
	}


	/**
	 * @return string
	 * @see UI_Form_Renderer_RenderType_SelfRenderingGroup Rendered like a regular element.
	 */
	public function __toString()
	{
		/* ... */
	}


	public function setTimeOptional(bool $optional = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLDateTimePicker.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;

/**
 * EXPERIMENTAL! Element that is used to handle generate HTML input with type date and time together.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLDateTimePicker extends HTML_QuickForm2_Container_Group
{
	public const ELEMENT_NAME_DATE = 'date';
	public const ELEMENT_NAME_TIME = 'time';
	public const CSS_FILE_NAME = 'forms/date-picker.css';

	public function getType(): string
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|array|NULL $value A date string in the format <code>Y-m-d H:i</code>,
	 *          a <code>DateTime</code> instance, or an array with keys
	 *          <code>date</code> and <code>time</code>.
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	public function getDateElement(): HTML_QuickForm2_Element_HTMLDatePicker
	{
		/* ... */
	}


	public function getTimeElement(): HTML_QuickForm2_Element_HTMLTimePicker
	{
		/* ... */
	}


	public static function parseDateTimeString(string $string, bool $timeOptional): ?array
	{
		/* ... */
	}


	public function getDate(): ?DateTime
	{
		/* ... */
	}


	public function getDateString(): ?string
	{
		/* ... */
	}


	public function getValue(): ?string
	{
		/* ... */
	}


	/**
	 * @return string
	 * @see UI_Form_Renderer_RenderType_SelfRenderingGroup Rendered like a regular element.
	 */
	public function __toString()
	{
		/* ... */
	}


	public function setTimeOptional(bool $optional = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLTimePicker.php`

```php
namespace ;

use Application\UI\Form\Element\DateTimePicker\BasicTime as BasicTime;

/**
 * Element that is used to handle generate HTML input with type time.
 * All browsers(except IE) will open time selection menu as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLTimePicker extends HTML_QuickForm2_Element_Input
{
	public const REGEX_GROUP_TIME = '([0-9]{2}):([0-9]{2})';

	public function getType(): string
	{
		/* ... */
	}


	public function getTime(): ?BasicTime
	{
		/* ... */
	}


	public function getHour(): ?int
	{
		/* ... */
	}


	public function getMinutes(): ?int
	{
		/* ... */
	}


	public static function parseTimeString(string $string): ?array
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|BasicTime|NULL $value
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/HTMLTimePicker.php`

```php
namespace ;

use Application\UI\Form\Element\DateTimePicker\BasicTime as BasicTime;

/**
 * Element that is used to handle generate HTML input with type time.
 * All browsers(except IE) will open time selection menu as input
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class HTML_QuickForm2_Element_HTMLTimePicker extends HTML_QuickForm2_Element_Input
{
	public const REGEX_GROUP_TIME = '([0-9]{2}):([0-9]{2})';

	public function getType(): string
	{
		/* ... */
	}


	public function getTime(): ?BasicTime
	{
		/* ... */
	}


	public function getHour(): ?int
	{
		/* ... */
	}


	public function getMinutes(): ?int
	{
		/* ... */
	}


	public static function parseTimeString(string $string): ?array
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	/**
	 * @param string|DateTime|BasicTime|NULL $value
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/ImageUploader.php`

```php
namespace ;

use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper_Exception as ImageHelper_Exception;
use AppUtils\ImageHelper_Size as ImageHelper_Size;
use AppUtils\NumberInfo as NumberInfo;
use AppUtils\OutputBuffering as OutputBuffering;
use Application\Media\Collection\MediaCollection as MediaCollection;

/**
 * Element that is used to handle SPIN image uploads: handles an image upload
 * in its own dialog window and processes image transformations directly.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ImageUploader extends HTML_QuickForm2_Element_Input
{
	public const THUMBNAIL_WIDTH = 75;
	public const THUMBNAIL_HEIGHT = 75;

	/**
	 * Retrieves the default, empty value for image uploader elements.
	 * This is an array with three keys:
	 *
	 * - name
	 * - state
	 * - id
	 *
	 * @return array{name:string,state:string,id:string}
	 */
	public static function getDefaultData(): array
	{
		/* ... */
	}


	/**
	 * Overridden to allow storing the image upload field's
	 * array value from the three input elements it is made of.
	 *
	 * @return $this
	 * @throws Application_Exception
	 *
	 * @see HTML_QuickForm2_Element_Input::setValue()
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Override regular method: image upload fields return an array
	 * value with three keys: name, state and id.
	 *
	 * @see HTML_QuickForm2_Node::getValue()
	 */
	public function getValue()
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	/**
	 * Retrieves the media instance for the current value of the uploader, if any.
	 * @return Application_Media_DocumentInterface|NULL
	 */
	public function getMedia(): ?Application_Media_DocumentInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the media document for the specified uploader value.
	 * @param array|NULL $value
	 * @return Application_Media_DocumentInterface|NULL
	 */
	public static function getMediaByValue(?array $value): ?Application_Media_DocumentInterface
	{
		/* ... */
	}


	public static function isValidMedia($value)
	{
		/* ... */
	}


	/**
	 * Checks if the specified image file name is a supported
	 * image type by checking its extension. Returns the extension
	 * if it is supported, false otherwise.
	 *
	 * @param string $fileName
	 * @return boolean|string
	 */
	public static function isSupportedFile($fileName)
	{
		/* ... */
	}


	/**
	 * Upgrades the uploaded media file to a regular media document
	 * if a file has been uploaded. Has no effect otherwise, and can
	 * safely be called if the media has already been upgraded.
	 *
	 * Note: This is called automatically by the form in the
	 * postValidation routine, and does not need to be called manually.
	 *
	 * @see UI_Form::postValidation()
	 */
	public function upgradeMedia(): void
	{
		/* ... */
	}


	/**
	 * Ensures that the image dimensions are even sized (width and height).
	 */
	public function addRuleEvenSized()
	{
		/* ... */
	}


	/**
	 * Adds a rule for a minimum image size, with optionally a recommendation
	 * to upload it double that.
	 *
	 * @param int $width
	 * @param int $height
	 * @return HTML_QuickForm2_Rule_Callback
	 */
	public function addRuleMinSize($width = 0, $height = 0)
	{
		/* ... */
	}


	/**
	 * Adds a recommendation to upload the image in double
	 * resolution. Requires the min size rule to be added.
	 */
	public function makeDoubleResolution()
	{
		/* ... */
	}


	public function isDoubleResolution()
	{
		/* ... */
	}


	public function hasMinSizeRule()
	{
		/* ... */
	}


	public function getAutoComments()
	{
		/* ... */
	}


	/**
	 * Checks whether the element has an even sized rule.
	 * @return boolean
	 */
	public function hasEvenSizedRule()
	{
		/* ... */
	}


	public function validate_evenSized($value, HTML_QuickForm2_Rule_Callback $rule): bool
	{
		/* ... */
	}


	public function validate_minSize($value, $width, $height, HTML_QuickForm2_Rule_Callback $rule): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/ImageUploader.php`

```php
namespace ;

use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper_Exception as ImageHelper_Exception;
use AppUtils\ImageHelper_Size as ImageHelper_Size;
use AppUtils\NumberInfo as NumberInfo;
use AppUtils\OutputBuffering as OutputBuffering;
use Application\Media\Collection\MediaCollection as MediaCollection;

/**
 * Element that is used to handle SPIN image uploads: handles an image upload
 * in its own dialog window and processes image transformations directly.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ImageUploader extends HTML_QuickForm2_Element_Input
{
	public const THUMBNAIL_WIDTH = 75;
	public const THUMBNAIL_HEIGHT = 75;

	/**
	 * Retrieves the default, empty value for image uploader elements.
	 * This is an array with three keys:
	 *
	 * - name
	 * - state
	 * - id
	 *
	 * @return array{name:string,state:string,id:string}
	 */
	public static function getDefaultData(): array
	{
		/* ... */
	}


	/**
	 * Overridden to allow storing the image upload field's
	 * array value from the three input elements it is made of.
	 *
	 * @return $this
	 * @throws Application_Exception
	 *
	 * @see HTML_QuickForm2_Element_Input::setValue()
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Override regular method: image upload fields return an array
	 * value with three keys: name, state and id.
	 *
	 * @see HTML_QuickForm2_Node::getValue()
	 */
	public function getValue()
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	/**
	 * Retrieves the media instance for the current value of the uploader, if any.
	 * @return Application_Media_DocumentInterface|NULL
	 */
	public function getMedia(): ?Application_Media_DocumentInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the media document for the specified uploader value.
	 * @param array|NULL $value
	 * @return Application_Media_DocumentInterface|NULL
	 */
	public static function getMediaByValue(?array $value): ?Application_Media_DocumentInterface
	{
		/* ... */
	}


	public static function isValidMedia($value)
	{
		/* ... */
	}


	/**
	 * Checks if the specified image file name is a supported
	 * image type by checking its extension. Returns the extension
	 * if it is supported, false otherwise.
	 *
	 * @param string $fileName
	 * @return boolean|string
	 */
	public static function isSupportedFile($fileName)
	{
		/* ... */
	}


	/**
	 * Upgrades the uploaded media file to a regular media document
	 * if a file has been uploaded. Has no effect otherwise, and can
	 * safely be called if the media has already been upgraded.
	 *
	 * Note: This is called automatically by the form in the
	 * postValidation routine, and does not need to be called manually.
	 *
	 * @see UI_Form::postValidation()
	 */
	public function upgradeMedia(): void
	{
		/* ... */
	}


	/**
	 * Ensures that the image dimensions are even sized (width and height).
	 */
	public function addRuleEvenSized()
	{
		/* ... */
	}


	/**
	 * Adds a rule for a minimum image size, with optionally a recommendation
	 * to upload it double that.
	 *
	 * @param int $width
	 * @param int $height
	 * @return HTML_QuickForm2_Rule_Callback
	 */
	public function addRuleMinSize($width = 0, $height = 0)
	{
		/* ... */
	}


	/**
	 * Adds a recommendation to upload the image in double
	 * resolution. Requires the min size rule to be added.
	 */
	public function makeDoubleResolution()
	{
		/* ... */
	}


	public function isDoubleResolution()
	{
		/* ... */
	}


	public function hasMinSizeRule()
	{
		/* ... */
	}


	public function getAutoComments()
	{
		/* ... */
	}


	/**
	 * Checks whether the element has an even sized rule.
	 * @return boolean
	 */
	public function hasEvenSizedRule()
	{
		/* ... */
	}


	public function validate_evenSized($value, HTML_QuickForm2_Rule_Callback $rule): bool
	{
		/* ... */
	}


	public function validate_minSize($value, $width, $height, HTML_QuickForm2_Rule_Callback $rule): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Multiselect.php`

```php
namespace ;

/**
 * Bootstrap-based multiple select element that implements the
 * interface of the bootstrap multiselect plugin.
 *
 * @package Application
 * @subpackage Forms
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see https://github.com/davidstutz/bootstrap-multiselect
 * @see http://davidstutz.github.io/bootstrap-multiselect
 */
class HTML_QuickForm2_Element_Multiselect extends HTML_QuickForm2_Element_Select
{
	public function __toString()
	{
		/* ... */
	}


	public function setFilterPlaceholder($text)
	{
		/* ... */
	}


	public function enableFiltering()
	{
		/* ... */
	}


	public function setMaxHeight($height)
	{
		/* ... */
	}


	public function setMultiOption($name, $value)
	{
		/* ... */
	}


	public function makeBlock()
	{
		/* ... */
	}


	public function enableSelectAll()
	{
		/* ... */
	}


	/**
	 * When the element is shown inline, the dropdown menu is
	 * opened as a block element in the page, not as a hover
	 * menu.
	 */
	public function makeInline()
	{
		/* ... */
	}


	/**
	 * Adds a class to the container element of the button and dropdown menu.
	 * Use this when you need to be able to style the dropdown menu, for example,
	 * since by default it is not wrapped in another element.
	 *
	 * @param string $className
	 * @return HTML_QuickForm2_Element_Multiselect
	 */
	public function addContainerClass($className)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Multiselect.php`

```php
namespace ;

/**
 * Bootstrap-based multiple select element that implements the
 * interface of the bootstrap multiselect plugin.
 *
 * @package Application
 * @subpackage Forms
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see https://github.com/davidstutz/bootstrap-multiselect
 * @see http://davidstutz.github.io/bootstrap-multiselect
 */
class HTML_QuickForm2_Element_Multiselect extends HTML_QuickForm2_Element_Select
{
	public function __toString()
	{
		/* ... */
	}


	public function setFilterPlaceholder($text)
	{
		/* ... */
	}


	public function enableFiltering()
	{
		/* ... */
	}


	public function setMaxHeight($height)
	{
		/* ... */
	}


	public function setMultiOption($name, $value)
	{
		/* ... */
	}


	public function makeBlock()
	{
		/* ... */
	}


	public function enableSelectAll()
	{
		/* ... */
	}


	/**
	 * When the element is shown inline, the dropdown menu is
	 * opened as a block element in the page, not as a hover
	 * menu.
	 */
	public function makeInline()
	{
		/* ... */
	}


	/**
	 * Adds a class to the container element of the button and dropdown menu.
	 * Use this when you need to be able to style the dropdown menu, for example,
	 * since by default it is not wrapped in another element.
	 *
	 * @param string $className
	 * @return HTML_QuickForm2_Element_Multiselect
	 */
	public function addContainerClass($className)
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Switch.php`

```php
namespace ;

use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_Switch extends HTML_QuickForm2_Element_Input implements ButtonSizeInterface
{
	use ButtonSizeTrait;

	public const ELEMENT_TYPE = 'switch';

	/**
	 * Sets the label for the ON state of the button
	 * @param string $label
	 * @return $this
	 */
	public function setOnLabel(string $label): self
	{
		/* ... */
	}


	/**
	 * Sets the label for the OFF state of the button
	 * @param string $label
	 * return $this
	 */
	public function setOffLabel(string $label): self
	{
		/* ... */
	}


	/**
	 * Checks if the switch is checked/active.
	 * @return boolean
	 */
	public function isChecked(): bool
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	/**
	 * Sets the clientside javascript statement to execute when
	 * the switch value changes.
	 *
	 * Example:
	 *
	 * ```php
	 * $switch->setOnchangeHandler(
	 *     'SomeClass.MethodName()',
	 *     '"string"'
	 * );
	 * ```
	 *
	 * @param string $statement
	 * @param string|null $data A javascript compatible value as a string.
	 * @return $this
	 */
	public function setOnchangeHandler(string $statement, ?string $data = null): self
	{
		/* ... */
	}


	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Makes the switch display "yes" and "no" instead of the
	 * default "on" and "off" button labels.
	 *
	 * NOTE: does not change the internal values: these stay
	 * "true" and "false", unless the $includeValue parameter
	 * is set to true.
	 *
	 * @param bool $includeValue If true, the values will be set to "yes" and "no".
	 * @return $this
	 */
	public function makeYesNo(bool $includeValue = false): self
	{
		/* ... */
	}


	public function makeEnabledDisabled(): self
	{
		/* ... */
	}


	public function makeActiveInactive(): self
	{
		/* ... */
	}


	public function makeOnOff(): self
	{
		/* ... */
	}


	public function setOnIcon(UI_Icon $icon): self
	{
		/* ... */
	}


	public function setOffIcon(UI_Icon $icon): self
	{
		/* ... */
	}


	public function makeWithIcons(bool $useIcons = true): self
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function setOnValue(string $value): self
	{
		/* ... */
	}


	public function setOffValue(string $value): self
	{
		/* ... */
	}


	public function setValues(string $onValue, string $offValue): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/Switch.php`

```php
namespace ;

use UI\Interfaces\ButtonSizeInterface as ButtonSizeInterface;
use UI\Traits\ButtonSizeTrait as ButtonSizeTrait;

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_Switch extends HTML_QuickForm2_Element_Input implements ButtonSizeInterface
{
	use ButtonSizeTrait;

	public const ELEMENT_TYPE = 'switch';

	/**
	 * Sets the label for the ON state of the button
	 * @param string $label
	 * @return $this
	 */
	public function setOnLabel(string $label): self
	{
		/* ... */
	}


	/**
	 * Sets the label for the OFF state of the button
	 * @param string $label
	 * return $this
	 */
	public function setOffLabel(string $label): self
	{
		/* ... */
	}


	/**
	 * Checks if the switch is checked/active.
	 * @return boolean
	 */
	public function isChecked(): bool
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}


	/**
	 * Sets the clientside javascript statement to execute when
	 * the switch value changes.
	 *
	 * Example:
	 *
	 * ```php
	 * $switch->setOnchangeHandler(
	 *     'SomeClass.MethodName()',
	 *     '"string"'
	 * );
	 * ```
	 *
	 * @param string $statement
	 * @param string|null $data A javascript compatible value as a string.
	 * @return $this
	 */
	public function setOnchangeHandler(string $statement, ?string $data = null): self
	{
		/* ... */
	}


	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Makes the switch display "yes" and "no" instead of the
	 * default "on" and "off" button labels.
	 *
	 * NOTE: does not change the internal values: these stay
	 * "true" and "false", unless the $includeValue parameter
	 * is set to true.
	 *
	 * @param bool $includeValue If true, the values will be set to "yes" and "no".
	 * @return $this
	 */
	public function makeYesNo(bool $includeValue = false): self
	{
		/* ... */
	}


	public function makeEnabledDisabled(): self
	{
		/* ... */
	}


	public function makeActiveInactive(): self
	{
		/* ... */
	}


	public function makeOnOff(): self
	{
		/* ... */
	}


	public function setOnIcon(UI_Icon $icon): self
	{
		/* ... */
	}


	public function setOffIcon(UI_Icon $icon): self
	{
		/* ... */
	}


	public function makeWithIcons(bool $useIcons = true): self
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function setOnValue(string $value): self
	{
		/* ... */
	}


	public function setOffValue(string $value): self
	{
		/* ... */
	}


	public function setValues(string $onValue, string $offValue): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/TreeSelect.php`

```php
namespace ;

use UI\Tree\TreeRenderer as TreeRenderer;

class HTML_QuickForm2_Element_TreeSelect extends HTML_QuickForm2_Element
{
	public const ERROR_TREE_NOT_SET = 149701;
	public const ELEMENT_TYPE = 'treeselect';

	public function setTree(TreeRenderer $renderer): self
	{
		/* ... */
	}


	public function getTree(): ?TreeRenderer
	{
		/* ... */
	}


	public function requireTree(): TreeRenderer
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}


	public function makeRequired(): self
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Fetches the currently selected values from the tree.
	 *
	 * @return string[]
	 * @throws HTML_QuickForm2_NotFoundException
	 */
	public function getRawValue(): array
	{
		/* ... */
	}


	/**
	 * @param array|mixed|NULL $value Flat, indexed array of tree node values or NULL for none. All other values are ignored.
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Sets the values that should be marked as selected
	 * in the tree.
	 *
	 * Note: This is an alias for {@see self::setValue()},
	 * as it is more intuitive to use.
	 *
	 * @param string[]|null $values
	 * @return $this
	 */
	public function setValues(?array $values): self
	{
		/* ... */
	}


	/**
	 * Fetches all currently selected values in the tree.
	 *
	 * Note: This is an alias for {@see self::getValue()},
	 * as it is more intuitive to use.
	 *
	 * @return string[]
	 */
	public function getValues(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/TreeSelect.php`

```php
namespace ;

use UI\Tree\TreeRenderer as TreeRenderer;

class HTML_QuickForm2_Element_TreeSelect extends HTML_QuickForm2_Element
{
	public const ERROR_TREE_NOT_SET = 149701;
	public const ELEMENT_TYPE = 'treeselect';

	public function setTree(TreeRenderer $renderer): self
	{
		/* ... */
	}


	public function getTree(): ?TreeRenderer
	{
		/* ... */
	}


	public function requireTree(): TreeRenderer
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}


	public function makeRequired(): self
	{
		/* ... */
	}


	public function getType(): string
	{
		/* ... */
	}


	/**
	 * Fetches the currently selected values from the tree.
	 *
	 * @return string[]
	 * @throws HTML_QuickForm2_NotFoundException
	 */
	public function getRawValue(): array
	{
		/* ... */
	}


	/**
	 * @param array|mixed|NULL $value Flat, indexed array of tree node values or NULL for none. All other values are ignored.
	 * @return $this
	 */
	public function setValue($value): self
	{
		/* ... */
	}


	/**
	 * Sets the values that should be marked as selected
	 * in the tree.
	 *
	 * Note: This is an alias for {@see self::setValue()},
	 * as it is more intuitive to use.
	 *
	 * @param string[]|null $values
	 * @return $this
	 */
	public function setValues(?array $values): self
	{
		/* ... */
	}


	/**
	 * Fetches all currently selected values in the tree.
	 *
	 * Note: This is an alias for {@see self::getValue()},
	 * as it is more intuitive to use.
	 *
	 * @return string[]
	 */
	public function getValues(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/UIButton.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_UIButton extends HTML_QuickForm2_Element_Button
{
	public function getButtonInstance(): UI_Button
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function makeSubmit(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function makeDangerous(): self
	{
		/* ... */
	}


	public function makePrimary(): self
	{
		/* ... */
	}


	public function makeSuccess(): self
	{
		/* ... */
	}


	public function makeDeveloper(): self
	{
		/* ... */
	}


	public function makeWarning(): self
	{
		/* ... */
	}


	public function makeInfo(): self
	{
		/* ... */
	}


	public function makeInverse(): self
	{
		/* ... */
	}


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $text
	 * @return $this
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * NOTE: This is not type hinted on purpose
	 * to stay compatible with the
	 * `HTML_Common2::hasClass()` method.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasClass(string $name): bool
	{
		/* ... */
	}


	public function hasClasses(): bool
	{
		/* ... */
	}


	public function addClasses(array $names): self
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function classesToString(): string
	{
		/* ... */
	}


	public function classesToAttribute(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param bool $withInput
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	public function setIcon(?UI_Icon $icon): self
	{
		/* ... */
	}


	public function hasIcon(): bool
	{
		/* ... */
	}


	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	public function getLockReason(): string
	{
		/* ... */
	}


	public function makeLockable($lockable = true): self
	{
		/* ... */
	}


	public function isLockable(): bool
	{
		/* ... */
	}


	public function lock($reason): self
	{
		/* ... */
	}


	public function unlock(): self
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/UIButton.php`

```php
namespace ;

use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_UIButton extends HTML_QuickForm2_Element_Button
{
	public function getButtonInstance(): UI_Button
	{
		/* ... */
	}


	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label): self
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function makeSubmit(): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $title
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTitle($title): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $tooltip
	 * @return $this
	 * @throws UI_Exception
	 */
	public function setTooltip($tooltip): self
	{
		/* ... */
	}


	public function makeDangerous(): self
	{
		/* ... */
	}


	public function makePrimary(): self
	{
		/* ... */
	}


	public function makeSuccess(): self
	{
		/* ... */
	}


	public function makeDeveloper(): self
	{
		/* ... */
	}


	public function makeWarning(): self
	{
		/* ... */
	}


	public function makeInfo(): self
	{
		/* ... */
	}


	public function makeInverse(): self
	{
		/* ... */
	}


	/**
	 * @param string $statement
	 * @return $this
	 */
	public function click(string $statement): self
	{
		/* ... */
	}


	/**
	 * @param string|AdminURLInterface $url
	 * @param string $target
	 * @return $this
	 */
	public function link($url, string $target = ''): self
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $text
	 * @return $this
	 */
	public function setLoadingText($text): self
	{
		/* ... */
	}


	public function getTooltip(): string
	{
		/* ... */
	}


	/**
	 * NOTE: This is not type hinted on purpose
	 * to stay compatible with the
	 * `HTML_Common2::hasClass()` method.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasClass(string $name): bool
	{
		/* ... */
	}


	public function hasClasses(): bool
	{
		/* ... */
	}


	public function addClasses(array $names): self
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function classesToString(): string
	{
		/* ... */
	}


	public function classesToAttribute(): string
	{
		/* ... */
	}


	/**
	 * @param string|number|UI_Renderable_Interface|NULL $message
	 * @param bool $withInput
	 * @return $this
	 * @throws UI_Exception
	 */
	public function makeConfirm($message, bool $withInput = false): self
	{
		/* ... */
	}


	public function getConfirmMessage(): UI_ClientConfirmable_Message
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	public function isClickable(): bool
	{
		/* ... */
	}


	public function isLinked(): bool
	{
		/* ... */
	}


	public function getJavascript(): string
	{
		/* ... */
	}


	public function isConfirm(): bool
	{
		/* ... */
	}


	public function isDangerous(): bool
	{
		/* ... */
	}


	public function setIcon(?UI_Icon $icon): self
	{
		/* ... */
	}


	public function hasIcon(): bool
	{
		/* ... */
	}


	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	public function isLocked(): bool
	{
		/* ... */
	}


	public function getLockReason(): string
	{
		/* ... */
	}


	public function makeLockable($lockable = true): self
	{
		/* ... */
	}


	public function isLockable(): bool
	{
		/* ... */
	}


	public function lock($reason): self
	{
		/* ... */
	}


	public function unlock(): self
	{
		/* ... */
	}


	public function getPage(): UI_Page
	{
		/* ... */
	}


	public function getTheme(): UI_Themes_Theme
	{
		/* ... */
	}


	public function getUI(): UI
	{
		/* ... */
	}


	public function getInstanceID(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect.php`

```php
namespace ;

use HTML\QuickForm2\Element\Select\SelectOption as SelectOption;
use UI\ClientResourceCollection as ClientResourceCollection;
use UI\Form\Element\VisualSelect\ImageSet as ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption as VisualSelectOption;
use UI\Traits\ScriptInjectableInterface as ScriptInjectableInterface;
use UI\Traits\ScriptInjectableTrait as ScriptInjectableTrait;

/**
 * Select element that lets the user choose an item from
 * an image gallery (icons, for example). Includes filtering
 * by search term, and choosing the text value from a traditional
 * select element.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method HTML_QuickForm2_Element_VisualSelect_Optgroup addOptgroup($label, $attributes = null)
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 * @method VisualSelectOption prependOption($text, $value, $attributes = null)
 *
 * @see template_default_ui_forms_elements_visual_select
 */
class HTML_QuickForm2_Element_VisualSelect extends HTML_QuickForm2_Element_Select implements ScriptInjectableInterface
{
	use ScriptInjectableTrait;

	public const ERROR_IMAGE_SET_ALREADY_EXISTS = 130901;
	public const PROPERTY_SORTING_ENABLED = 'sorting-enabled';
	public const PROPERTY_PLEASE_SELECT_LABEL = 'please-select-label';
	public const PROPERTY_PLEASE_SELECT_ENABLED = 'please-select';

	/**
	 * Enables/disables the "Please select" entry to be able to
	 * not choose any of the proposed images.
	 *
	 * @param boolean $enabled
	 * @param string|number|UI_Renderable_Interface|NULL $selectLabel Optional when enabled: the label to use for the "Please select" image
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @throws UI_Exception
	 */
	public function setPleaseSelectEnabled(bool $enabled = true, $selectLabel = null): self
	{
		/* ... */
	}


	public function isPleaseSelectEnabled(): bool
	{
		/* ... */
	}


	/**
	 * @return string
	 * @throws UI_Exception
	 * @see template_default_ui_forms_elements_visual_select
	 */
	public function __toString()
	{
		/* ... */
	}


	public function hasPleaseSelect(): bool
	{
		/* ... */
	}


	public function getPleaseSelectLabel(): string
	{
		/* ... */
	}


	/**
	 * @param SelectOption[]|NULL $options
	 * @param VisualSelectOption[]|NULL $result
	 * @return VisualSelectOption[]
	 */
	public function getOptionsFlat(?array $options = null, ?array $result = null): array
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setSortingEnabled(bool $enabled = true): self
	{
		/* ... */
	}


	public function isGroupingEnabled(): bool
	{
		/* ... */
	}


	public function isFilteringEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Sets the default, large size of the thumbnails.
	 *
	 * @param int $size
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @see HTML_QuickForm2_Element_VisualSelect::setSmallThunbnailSize()
	 */
	public function setLargeThumbnailSize(int $size): self
	{
		/* ... */
	}


	/**
	 * Sets the size of the thumbnails for long lists with
	 * more items than the filtering threshold.
	 *
	 * @param int $size
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @see HTML_QuickForm2_Element_VisualSelect::setLargeThumbnailSize()
	 */
	public function setSmallThumbnailSize(int $size): self
	{
		/* ... */
	}


	/**
	 * Sets the amount of options from which the filtering
	 * element will be shown.
	 *
	 * @param int $amount
	 * @return HTML_QuickForm2_Element_VisualSelect
	 */
	public function setFilterThreshold(int $amount): self
	{
		/* ... */
	}


	public function getFilterThreshold(): int
	{
		/* ... */
	}


	/**
	 * Adds a checkered background to the images, to be able to
	 * see when they have transparency.
	 *
	 * @param bool $checkered
	 * @return $this
	 */
	public function makeCheckered(bool $checkered = true): self
	{
		/* ... */
	}


	public function isCheckered(): bool
	{
		/* ... */
	}


	public function getThumbnailSize(): int
	{
		/* ... */
	}


	/**
	 * Adds an image to select: simultaneously adds it to the
	 * select element and the list of images.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @param array<string,string> $attributes
	 * @return VisualSelectOption
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 */
	public function addImage($label, string $value, string $url, array $attributes = []): VisualSelectOption
	{
		/* ... */
	}


	public function addImageSet(string $id, string $label): ImageSet
	{
		/* ... */
	}


	/**
	 * @return ImageSet[]
	 */
	public function getImageSets(): array
	{
		/* ... */
	}


	public function getActiveImageSet(): ?ImageSet
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeBlock(): self
	{
		/* ... */
	}


	/**
	 * Adds a class to the container element of the button and dropdown menu.
	 * Use this when you need to be able to style the dropdown menu, for example,
	 * since by default it is not wrapped in another element.
	 *
	 * @param string $className
	 * @return $this
	 */
	public function addContainerClass(string $className): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect.php`

```php
namespace ;

use HTML\QuickForm2\Element\Select\SelectOption as SelectOption;
use UI\ClientResourceCollection as ClientResourceCollection;
use UI\Form\Element\VisualSelect\ImageSet as ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption as VisualSelectOption;
use UI\Traits\ScriptInjectableInterface as ScriptInjectableInterface;
use UI\Traits\ScriptInjectableTrait as ScriptInjectableTrait;

/**
 * Select element that lets the user choose an item from
 * an image gallery (icons, for example). Includes filtering
 * by search term, and choosing the text value from a traditional
 * select element.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method HTML_QuickForm2_Element_VisualSelect_Optgroup addOptgroup($label, $attributes = null)
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 * @method VisualSelectOption prependOption($text, $value, $attributes = null)
 *
 * @see template_default_ui_forms_elements_visual_select
 */
class HTML_QuickForm2_Element_VisualSelect extends HTML_QuickForm2_Element_Select implements ScriptInjectableInterface
{
	use ScriptInjectableTrait;

	public const ERROR_IMAGE_SET_ALREADY_EXISTS = 130901;
	public const PROPERTY_SORTING_ENABLED = 'sorting-enabled';
	public const PROPERTY_PLEASE_SELECT_LABEL = 'please-select-label';
	public const PROPERTY_PLEASE_SELECT_ENABLED = 'please-select';

	/**
	 * Enables/disables the "Please select" entry to be able to
	 * not choose any of the proposed images.
	 *
	 * @param boolean $enabled
	 * @param string|number|UI_Renderable_Interface|NULL $selectLabel Optional when enabled: the label to use for the "Please select" image
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @throws UI_Exception
	 */
	public function setPleaseSelectEnabled(bool $enabled = true, $selectLabel = null): self
	{
		/* ... */
	}


	public function isPleaseSelectEnabled(): bool
	{
		/* ... */
	}


	/**
	 * @return string
	 * @throws UI_Exception
	 * @see template_default_ui_forms_elements_visual_select
	 */
	public function __toString()
	{
		/* ... */
	}


	public function hasPleaseSelect(): bool
	{
		/* ... */
	}


	public function getPleaseSelectLabel(): string
	{
		/* ... */
	}


	/**
	 * @param SelectOption[]|NULL $options
	 * @param VisualSelectOption[]|NULL $result
	 * @return VisualSelectOption[]
	 */
	public function getOptionsFlat(?array $options = null, ?array $result = null): array
	{
		/* ... */
	}


	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setSortingEnabled(bool $enabled = true): self
	{
		/* ... */
	}


	public function isGroupingEnabled(): bool
	{
		/* ... */
	}


	public function isFilteringEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Sets the default, large size of the thumbnails.
	 *
	 * @param int $size
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @see HTML_QuickForm2_Element_VisualSelect::setSmallThunbnailSize()
	 */
	public function setLargeThumbnailSize(int $size): self
	{
		/* ... */
	}


	/**
	 * Sets the size of the thumbnails for long lists with
	 * more items than the filtering threshold.
	 *
	 * @param int $size
	 * @return HTML_QuickForm2_Element_VisualSelect
	 * @see HTML_QuickForm2_Element_VisualSelect::setLargeThumbnailSize()
	 */
	public function setSmallThumbnailSize(int $size): self
	{
		/* ... */
	}


	/**
	 * Sets the amount of options from which the filtering
	 * element will be shown.
	 *
	 * @param int $amount
	 * @return HTML_QuickForm2_Element_VisualSelect
	 */
	public function setFilterThreshold(int $amount): self
	{
		/* ... */
	}


	public function getFilterThreshold(): int
	{
		/* ... */
	}


	/**
	 * Adds a checkered background to the images, to be able to
	 * see when they have transparency.
	 *
	 * @param bool $checkered
	 * @return $this
	 */
	public function makeCheckered(bool $checkered = true): self
	{
		/* ... */
	}


	public function isCheckered(): bool
	{
		/* ... */
	}


	public function getThumbnailSize(): int
	{
		/* ... */
	}


	/**
	 * Adds an image to select: simultaneously adds it to the
	 * select element and the list of images.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @param array<string,string> $attributes
	 * @return VisualSelectOption
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 */
	public function addImage($label, string $value, string $url, array $attributes = []): VisualSelectOption
	{
		/* ... */
	}


	public function addImageSet(string $id, string $label): ImageSet
	{
		/* ... */
	}


	/**
	 * @return ImageSet[]
	 */
	public function getImageSets(): array
	{
		/* ... */
	}


	public function getActiveImageSet(): ?ImageSet
	{
		/* ... */
	}


	/**
	 * @return $this
	 */
	public function makeBlock(): self
	{
		/* ... */
	}


	/**
	 * Adds a class to the container element of the button and dropdown menu.
	 * Use this when you need to be able to style the dropdown menu, for example,
	 * since by default it is not wrapped in another element.
	 *
	 * @param string $className
	 * @return $this
	 */
	public function addContainerClass(string $className): self
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/ImageSet.php`

```php
namespace UI\Form\Element\VisualSelect;

use HTML_QuickForm2_Element_VisualSelect as HTML_QuickForm2_Element_VisualSelect;
use HTML_QuickForm2_Element_VisualSelect_Optgroup as HTML_QuickForm2_Element_VisualSelect_Optgroup;
use UI_Renderable_Interface as UI_Renderable_Interface;

class ImageSet
{
	public const ATTRIBUTE_SET_ID = 'data-image-set';
	public const PROPERTY_IMAGE_SET = 'image-set';

	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function addGroup(string $label): HTML_QuickForm2_Element_VisualSelect_Optgroup
	{
		/* ... */
	}


	/**
	 * Adds an image to select: simultaneously adds it to the
	 * select element and the list of images.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @return $this
	 */
	public function addImage($label, string $value, string $url): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/ImageSet.php`

```php
namespace UI\Form\Element\VisualSelect;

use HTML_QuickForm2_Element_VisualSelect as HTML_QuickForm2_Element_VisualSelect;
use HTML_QuickForm2_Element_VisualSelect_Optgroup as HTML_QuickForm2_Element_VisualSelect_Optgroup;
use UI_Renderable_Interface as UI_Renderable_Interface;

class ImageSet
{
	public const ATTRIBUTE_SET_ID = 'data-image-set';
	public const PROPERTY_IMAGE_SET = 'image-set';

	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function addGroup(string $label): HTML_QuickForm2_Element_VisualSelect_Optgroup
	{
		/* ... */
	}


	/**
	 * Adds an image to select: simultaneously adds it to the
	 * select element and the list of images.
	 *
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @return $this
	 */
	public function addImage($label, string $value, string $url): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/Optgroup.php`

```php
namespace ;

use UI\Form\Element\VisualSelect\ImageSet as ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption as VisualSelectOption;

/**
 * Custom option group that adds methods specific to the
 * visual selection element. Use the {@see HTML_QuickForm2_Element_VisualSelect_Optgroup::addImage()}
 * method to add images, instead of the regular `addOption()`
 * method.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 */
class HTML_QuickForm2_Element_VisualSelect_Optgroup extends HTML_QuickForm2_Element_Select_Optgroup
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @return VisualSelectOption
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 * @throws UI_Exception
	 */
	public function addImage($label, string $value, string $url): VisualSelectOption
	{
		/* ... */
	}


	public function getImageSetID(): string
	{
		/* ... */
	}


	public function getElementID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/Optgroup.php`

```php
namespace ;

use UI\Form\Element\VisualSelect\ImageSet as ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption as VisualSelectOption;

/**
 * Custom option group that adds methods specific to the
 * visual selection element. Use the {@see HTML_QuickForm2_Element_VisualSelect_Optgroup::addImage()}
 * method to add images, instead of the regular `addOption()`
 * method.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 */
class HTML_QuickForm2_Element_VisualSelect_Optgroup extends HTML_QuickForm2_Element_Select_Optgroup
{
	/**
	 * @param string|number|UI_Renderable_Interface|NULL $label
	 * @param string $value
	 * @param string $url
	 * @return VisualSelectOption
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 * @throws UI_Exception
	 */
	public function addImage($label, string $value, string $url): VisualSelectOption
	{
		/* ... */
	}


	public function getImageSetID(): string
	{
		/* ... */
	}


	public function getElementID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/VisualSelectOption.php`

```php
namespace UI\Form\Element\VisualSelect;

use HTML\QuickForm2\Element\Select\SelectOption as SelectOption;

class VisualSelectOption extends SelectOption
{
	public const ATTRIBUTE_IMAGE_URL = 'image-url';
	public const ATTRIBUTE_PLEASE_SELECT = 'data-please-select';

	public function getImageURL(): string
	{
		/* ... */
	}


	public function setImageURL(string $url): self
	{
		/* ... */
	}


	public function hasImage(): bool
	{
		/* ... */
	}


	public function isPleaseSelect(): bool
	{
		/* ... */
	}


	public function hasImageSet(): bool
	{
		/* ... */
	}


	public function getImageSetID(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Element/VisualSelect/VisualSelectOption.php`

```php
namespace UI\Form\Element\VisualSelect;

use HTML\QuickForm2\Element\Select\SelectOption as SelectOption;

class VisualSelectOption extends SelectOption
{
	public const ATTRIBUTE_IMAGE_URL = 'image-url';
	public const ATTRIBUTE_PLEASE_SELECT = 'data-please-select';

	public function getImageURL(): string
	{
		/* ... */
	}


	public function setImageURL(string $url): self
	{
		/* ... */
	}


	public function hasImage(): bool
	{
		/* ... */
	}


	public function isPleaseSelect(): bool
	{
		/* ... */
	}


	public function hasImageSet(): bool
	{
		/* ... */
	}


	public function getImageSetID(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/FormException.php`

```php
namespace UI\Form;

use Application_Exception as Application_Exception;

class FormException extends Application_Exception
{
}


```
###  Path: `/src/classes/UI/Form/Renderer.php`

```php
namespace ;

class UI_Form_Renderer extends UI_Renderable
{
	/**
	 * Unique ID of the rendered form within the request.
	 *
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	public function getForm(): UI_Form
	{
		/* ... */
	}


	public function setRegistryEnabled(bool $enabled = true): void
	{
		/* ... */
	}


	public function getRegistry(): UI_Form_Renderer_Registry
	{
		/* ... */
	}


	/**
	 * Registers a new form section: any elements rendered after
	 * this method call will be appended to that section.
	 *
	 * NOTE: The sections are created in the "Header" render type.
	 *
	 * @param UI_Form_Renderer_Sections_Section $section
	 *
	 * @see UI_Form_Renderer_RenderType_Header
	 */
	public function registerSection(UI_Form_Renderer_Sections_Section $section): void
	{
		/* ... */
	}


	public function getActiveSection(): ?UI_Form_Renderer_Sections_Section
	{
		/* ... */
	}


	public function getSections(): UI_Form_Renderer_Sections
	{
		/* ... */
	}


	public function getTabs(): UI_Form_Renderer_Tabs
	{
		/* ... */
	}


	/**
	 * Renders a form header of the specified level.
	 *
	 * @param string $label
	 * @param integer $level
	 * @return string
	 */
	public function renderHeader(string $label, int $level): string
	{
		/* ... */
	}


	/**
	 * Renders the elements from a filtered elements collection.
	 *
	 * @param UI_Form_Renderer_ElementFilter $filter
	 * @return string
	 */
	public function renderElements(UI_Form_Renderer_ElementFilter $filter): string
	{
		/* ... */
	}


	/**
	 * Filters the HTML_QuickForm rendered elements collection,
	 * to keep only the relevant elements and to convert them
	 * to the renderer element definition instances.
	 *
	 * @param array $elements
	 * @param int $level
	 * @return UI_Form_Renderer_ElementFilter
	 */
	public function filterElements(array $elements, int $level = 0): UI_Form_Renderer_ElementFilter
	{
		/* ... */
	}


	/**
	 * Registers a "submit" button in the form.
	 *
	 * @param UI_Form_Renderer_RenderType_Button $button
	 */
	public function registerButton(UI_Form_Renderer_RenderType_Button $button): void
	{
		/* ... */
	}


	public function getRootElements(): UI_Form_Renderer_ElementFilter
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\Application as Application;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;

class UI_Form_Renderer_CommentGenerator
{
	public const PROPERTY_COMMENTS_CALLBACK = 'comments-callback';
	public const PROPERTY_MARKDOWN_SUPPORT = 'markdown_support';

	public function getComment(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use Application\Application as Application;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;

class UI_Form_Renderer_CommentGenerator
{
	public const PROPERTY_COMMENTS_CALLBACK = 'comments-callback';
	public const PROPERTY_MARKDOWN_SUPPORT = 'markdown_support';

	public function getComment(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType.php`

```php
namespace ;

abstract class UI_Form_Renderer_CommentGenerator_DataType
{
	abstract public function addComments(): void;
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType.php`

```php
namespace ;

abstract class UI_Form_Renderer_CommentGenerator_DataType
{
	abstract public function addComments(): void;
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Date.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Date extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Date.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Date extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Float.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Float extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Float.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Float extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/ISODate.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_ISODate extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/ISODate.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_ISODate extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Integer.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Integer extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/Integer.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_Integer extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/RegexHint.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_RegexHint extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/CommentGenerator/DataType/RegexHint.php`

```php
namespace ;

class UI_Form_Renderer_CommentGenerator_DataType_RegexHint extends UI_Form_Renderer_CommentGenerator_DataType
{
	public function addComments(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Element.php`

```php
namespace ;

/**
 * Renders the markup for form elements, and provides
 * utility methods to customize this markup. This class
 * is provided as argument to the element's render callback
 * functions.
 *
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form::addRenderCallback()
 * @see UI_Form_Renderer_RenderType_Default
 */
class UI_Form_Renderer_Element extends UI_Renderable
{
	const HTML_BELOW_COMMENT = 'below_comment';
	const HTML_ABOVE_CONTROL = 'above_control';
	const HTML_BELOW_CONTROL = 'below_control';

	/**
	 * Retrieves the form element being rendered.
	 * @return HTML_QuickForm2_Node
	 */
	public function getFormElement(): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function isFrozen(): bool
	{
		/* ... */
	}


	public function isStandalone(): bool
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getLabelID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the label with additional markup to
	 * ensure that it can word wrap correctly, even if
	 * the label is filled with underscores.
	 *
	 * @return string
	 */
	public function getLabelForHTML(): string
	{
		/* ... */
	}


	public function getElementHTML(): string
	{
		/* ... */
	}


	/**
	 * @param string $html
	 * @return $this
	 */
	public function setElementHTML(string $html): self
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class to the form element's control
	 * group container DIV element.
	 *
	 * @param string $name
	 * @return UI_Form_Renderer_Element
	 *
	 * @see UI_Form_Renderer_RenderType_Default
	 */
	public function addControlGroupClass(string $name): UI_Form_Renderer_Element
	{
		/* ... */
	}


	/**
	 * Adds custom HTML to the element at the specified position.
	 *
	 * @param string $html
	 * @param string $position
	 * @param bool $whenFrozen Whether to add this when the element is frozen
	 * @see UI_Form_Renderer_Element::HTML_BELOW_COMMENT
	 * @see UI_Form_Renderer_Element::HTML_ABOVE_CONTROL
	 * @see UI_Form_Renderer_Element::HTML_BELOW_CONTROL
	 */
	public function addHTML(
		string $html,
		string $position = self::HTML_BELOW_CONTROL,
		bool $whenFrozen = false,
	): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLBelowComment(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLBelowControl(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLAboveControl(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	/**
	 * Adds an icon that is shown next to the label of the element.
	 *
	 * @param UI_Icon $icon
	 * @return UI_Form_Renderer_Element
	 */
	public function addIcon(UI_Icon $icon): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function getFormRenderer(): UI_Form_Renderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Element.php`

```php
namespace ;

/**
 * Renders the markup for form elements, and provides
 * utility methods to customize this markup. This class
 * is provided as argument to the element's render callback
 * functions.
 *
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form::addRenderCallback()
 * @see UI_Form_Renderer_RenderType_Default
 */
class UI_Form_Renderer_Element extends UI_Renderable
{
	const HTML_BELOW_COMMENT = 'below_comment';
	const HTML_ABOVE_CONTROL = 'above_control';
	const HTML_BELOW_CONTROL = 'below_control';

	/**
	 * Retrieves the form element being rendered.
	 * @return HTML_QuickForm2_Node
	 */
	public function getFormElement(): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function isFrozen(): bool
	{
		/* ... */
	}


	public function isStandalone(): bool
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getLabelID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the label with additional markup to
	 * ensure that it can word wrap correctly, even if
	 * the label is filled with underscores.
	 *
	 * @return string
	 */
	public function getLabelForHTML(): string
	{
		/* ... */
	}


	public function getElementHTML(): string
	{
		/* ... */
	}


	/**
	 * @param string $html
	 * @return $this
	 */
	public function setElementHTML(string $html): self
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}


	public function getValue(): string
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	/**
	 * Adds a class to the form element's control
	 * group container DIV element.
	 *
	 * @param string $name
	 * @return UI_Form_Renderer_Element
	 *
	 * @see UI_Form_Renderer_RenderType_Default
	 */
	public function addControlGroupClass(string $name): UI_Form_Renderer_Element
	{
		/* ... */
	}


	/**
	 * Adds custom HTML to the element at the specified position.
	 *
	 * @param string $html
	 * @param string $position
	 * @param bool $whenFrozen Whether to add this when the element is frozen
	 * @see UI_Form_Renderer_Element::HTML_BELOW_COMMENT
	 * @see UI_Form_Renderer_Element::HTML_ABOVE_CONTROL
	 * @see UI_Form_Renderer_Element::HTML_BELOW_CONTROL
	 */
	public function addHTML(
		string $html,
		string $position = self::HTML_BELOW_CONTROL,
		bool $whenFrozen = false,
	): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLBelowComment(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLBelowControl(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function addHTMLAboveControl(string $html, bool $whenFrozen = false): UI_Form_Renderer_Element
	{
		/* ... */
	}


	/**
	 * Adds an icon that is shown next to the label of the element.
	 *
	 * @param UI_Icon $icon
	 * @return UI_Form_Renderer_Element
	 */
	public function addIcon(UI_Icon $icon): UI_Form_Renderer_Element
	{
		/* ... */
	}


	public function getFormRenderer(): UI_Form_Renderer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/ElementCallback.php`

```php

```
###  Path: `/src/classes/UI/Form/Renderer/ElementCallback.php`

```php

```
###  Path: `/src/classes/UI/Form/Renderer/ElementFilter.php`

```php
namespace ;

class UI_Form_Renderer_ElementFilter
{
	public const ERROR_UNKNOWN_ELEMENT = 64501;

	public function getByID(string $id): UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getRegularElementIDs(): array
	{
		/* ... */
	}


	/**
	 * @return UI_Form_Renderer_ElementFilter_RenderDef[]
	 */
	public function getFiltered(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/ElementFilter.php`

```php
namespace ;

class UI_Form_Renderer_ElementFilter
{
	public const ERROR_UNKNOWN_ELEMENT = 64501;

	public function getByID(string $id): UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getRegularElementIDs(): array
	{
		/* ... */
	}


	/**
	 * @return UI_Form_Renderer_ElementFilter_RenderDef[]
	 */
	public function getFiltered(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/ElementFilter/RenderDef.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;

class UI_Form_Renderer_ElementFilter_RenderDef
{
	/**
	 * The level at which this form element is being rendered,
	 * zero-based, zero being the form itself. Elements in
	 * groups and the like have accordingly higher levels.
	 *
	 * @return int
	 */
	public function getLevel(): int
	{
		/* ... */
	}


	public function getRenderer(): UI_Form_Renderer
	{
		/* ... */
	}


	public function hasError(): bool
	{
		/* ... */
	}


	public function isSection(): bool
	{
		/* ... */
	}


	public function getErrorMessage(): string
	{
		/* ... */
	}


	public function getElementLabel(): string
	{
		/* ... */
	}


	public function getElementTypeID(): string
	{
		/* ... */
	}


	public function getTypeClass(): string
	{
		/* ... */
	}


	public function isRegularElement(): bool
	{
		/* ... */
	}


	public function isStandalone(): bool
	{
		/* ... */
	}


	public function isContainerElement(): bool
	{
		/* ... */
	}


	public function getElementComment(): string
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}


	public function getElementValue(): string
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	public function getElementID(): string
	{
		/* ... */
	}


	public function getLabelID(): string
	{
		/* ... */
	}


	public function getElementHTML(): string
	{
		/* ... */
	}


	public function getRel(): string
	{
		/* ... */
	}


	public function getRelValues(): array
	{
		/* ... */
	}


	public function isDummy(): bool
	{
		/* ... */
	}


	public function isLast(): bool
	{
		/* ... */
	}


	public function makeLast(): void
	{
		/* ... */
	}


	public function setSection(UI_Form_Renderer_Sections_Section $section): void
	{
		/* ... */
	}


	public function getSection(): ?UI_Form_Renderer_Sections_Section
	{
		/* ... */
	}


	public function getSectionID(): string
	{
		/* ... */
	}


	public function includeInRegistry(): bool
	{
		/* ... */
	}


	public function getTypeRenderer(): UI_Form_Renderer_RenderType
	{
		/* ... */
	}


	public function getElement(): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Retrieves a property of the element available only at runtime.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getRuntimeProperty(string $name)
	{
		/* ... */
	}


	public function getAttribute(string $name): string
	{
		/* ... */
	}


	public function resolveComments(): string
	{
		/* ... */
	}


	public function getSubDefs(): array
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function isFrozen(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/ElementFilter/RenderDef.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;

class UI_Form_Renderer_ElementFilter_RenderDef
{
	/**
	 * The level at which this form element is being rendered,
	 * zero-based, zero being the form itself. Elements in
	 * groups and the like have accordingly higher levels.
	 *
	 * @return int
	 */
	public function getLevel(): int
	{
		/* ... */
	}


	public function getRenderer(): UI_Form_Renderer
	{
		/* ... */
	}


	public function hasError(): bool
	{
		/* ... */
	}


	public function isSection(): bool
	{
		/* ... */
	}


	public function getErrorMessage(): string
	{
		/* ... */
	}


	public function getElementLabel(): string
	{
		/* ... */
	}


	public function getElementTypeID(): string
	{
		/* ... */
	}


	public function getTypeClass(): string
	{
		/* ... */
	}


	public function isRegularElement(): bool
	{
		/* ... */
	}


	public function isStandalone(): bool
	{
		/* ... */
	}


	public function isContainerElement(): bool
	{
		/* ... */
	}


	public function getElementComment(): string
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}


	public function getElementValue(): string
	{
		/* ... */
	}


	public function isStructural(): bool
	{
		/* ... */
	}


	public function getElementID(): string
	{
		/* ... */
	}


	public function getLabelID(): string
	{
		/* ... */
	}


	public function getElementHTML(): string
	{
		/* ... */
	}


	public function getRel(): string
	{
		/* ... */
	}


	public function getRelValues(): array
	{
		/* ... */
	}


	public function isDummy(): bool
	{
		/* ... */
	}


	public function isLast(): bool
	{
		/* ... */
	}


	public function makeLast(): void
	{
		/* ... */
	}


	public function setSection(UI_Form_Renderer_Sections_Section $section): void
	{
		/* ... */
	}


	public function getSection(): ?UI_Form_Renderer_Sections_Section
	{
		/* ... */
	}


	public function getSectionID(): string
	{
		/* ... */
	}


	public function includeInRegistry(): bool
	{
		/* ... */
	}


	public function getTypeRenderer(): UI_Form_Renderer_RenderType
	{
		/* ... */
	}


	public function getElement(): HTML_QuickForm2_Node
	{
		/* ... */
	}


	/**
	 * Retrieves a property of the element available only at runtime.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getRuntimeProperty(string $name)
	{
		/* ... */
	}


	public function getAttribute(string $name): string
	{
		/* ... */
	}


	public function resolveComments(): string
	{
		/* ... */
	}


	public function getSubDefs(): array
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function isFrozen(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Registry.php`

```php
namespace ;

class UI_Form_Renderer_Registry implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function setEnabled(bool $enabled): void
	{
		/* ... */
	}


	public function injectJS(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Registry.php`

```php
namespace ;

class UI_Form_Renderer_Registry implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function setEnabled(bool $enabled): void
	{
		/* ... */
	}


	public function injectJS(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType.php`

```php
namespace ;

abstract class UI_Form_Renderer_RenderType
{
	public function getRenderDef(): UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function getRenderer(): UI_Form_Renderer
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	abstract public function includeInRegistry(): bool;


	public function renderMarkupError(): string
	{
		/* ... */
	}


	public function renderMarkupComments(): string
	{
		/* ... */
	}


	public function getHTML(): string
	{
		/* ... */
	}


	public function renderMarkupHeader(string $label, int $level): string
	{
		/* ... */
	}


	public function getSubElements(): UI_Form_Renderer_ElementFilter
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType.php`

```php
namespace ;

abstract class UI_Form_Renderer_RenderType
{
	public function getRenderDef(): UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function getRenderer(): UI_Form_Renderer
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	abstract public function includeInRegistry(): bool;


	public function renderMarkupError(): string
	{
		/* ... */
	}


	public function renderMarkupComments(): string
	{
		/* ... */
	}


	public function getHTML(): string
	{
		/* ... */
	}


	public function renderMarkupHeader(string $label, int $level): string
	{
		/* ... */
	}


	public function getSubElements(): UI_Form_Renderer_ElementFilter
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Button.php`

```php
namespace ;

/**
 * A button is detected by checking the element's `rel`
 * attribute. This is set automatically by form element
 * type in the render def.
 *
 * @package User Interface
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form::REL_BUTTON
 * @see UI_Form_Renderer_ElementFilter_RenderDef::$relByType
 * @see UI_Form_Renderer_ElementFilter_RenderDef::getTypeClass()
 */
class UI_Form_Renderer_RenderType_Button extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Button.php`

```php
namespace ;

/**
 * A button is detected by checking the element's `rel`
 * attribute. This is set automatically by form element
 * type in the render def.
 *
 * @package User Interface
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form::REL_BUTTON
 * @see UI_Form_Renderer_ElementFilter_RenderDef::$relByType
 * @see UI_Form_Renderer_ElementFilter_RenderDef::getTypeClass()
 */
class UI_Form_Renderer_RenderType_Button extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Default.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;

/**
 * Renderer for all non-special form elements: this handles all regular
 * HTML QuickForm elements (like text input, select, etc...).
 *
 * These elements can be customized using callbacks, via the
 * {@see UI_Form_Renderer_Element} class. To add a render callback for
 * an element, see the {@see UI_Form::addRenderCallback()} method.
 *
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form_Renderer_Element
 * @see UI_Form::addRenderCallback()
 */
class UI_Form_Renderer_RenderType_Default extends UI_Form_Renderer_RenderType implements ClassableInterface
{
	use ClassableTrait;

	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Default.php`

```php
namespace ;

use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;

/**
 * Renderer for all non-special form elements: this handles all regular
 * HTML QuickForm elements (like text input, select, etc...).
 *
 * These elements can be customized using callbacks, via the
 * {@see UI_Form_Renderer_Element} class. To add a render callback for
 * an element, see the {@see UI_Form::addRenderCallback()} method.
 *
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Form_Renderer_Element
 * @see UI_Form::addRenderCallback()
 */
class UI_Form_Renderer_RenderType_Default extends UI_Form_Renderer_RenderType implements ClassableInterface
{
	use ClassableTrait;

	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Group.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Group extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Group.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Group extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Header.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Header extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Header.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Header extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Hint.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Hint extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Hint.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Hint extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Html.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Html extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Html.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Html extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/LayoutlessGroup.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_LayoutlessGroup extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/LayoutlessGroup.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_LayoutlessGroup extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Paragraph.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Paragraph extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Paragraph.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Paragraph extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Radio.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Radio extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Radio.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Radio extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/SelfRenderingGroup.php`

```php
namespace ;

/**
 * A group of elements that has a custom renderer:
 * The group's HTML is rendered by the group instance
 * itself. It is effectively treated as a default,
 * single element.
 *
 * @package UserInterface
 * @subpackage Forms
 */
class UI_Form_Renderer_RenderType_SelfRenderingGroup extends UI_Form_Renderer_RenderType_Default
{
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/SelfRenderingGroup.php`

```php
namespace ;

/**
 * A group of elements that has a custom renderer:
 * The group's HTML is rendered by the group instance
 * itself. It is effectively treated as a default,
 * single element.
 *
 * @package UserInterface
 * @subpackage Forms
 */
class UI_Form_Renderer_RenderType_SelfRenderingGroup extends UI_Form_Renderer_RenderType_Default
{
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Static.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Static extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Static.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Static extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Subheader.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Subheader extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Subheader.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Subheader extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Tab.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Tab extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/RenderType/Tab.php`

```php
namespace ;

class UI_Form_Renderer_RenderType_Tab extends UI_Form_Renderer_RenderType
{
	public function includeInRegistry(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Sections.php`

```php
namespace ;

class UI_Form_Renderer_Sections
{
	public function create(UI_Form_Renderer_ElementFilter_RenderDef $renderDef): UI_Form_Renderer_Sections_Section
	{
		/* ... */
	}


	/**
	 * @return UI_Form_Renderer_Sections_Section[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Sections.php`

```php
namespace ;

class UI_Form_Renderer_Sections
{
	public function create(UI_Form_Renderer_ElementFilter_RenderDef $renderDef): UI_Form_Renderer_Sections_Section
	{
		/* ... */
	}


	/**
	 * @return UI_Form_Renderer_Sections_Section[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Sections/Section.php`

```php
namespace ;

class UI_Form_Renderer_Sections_Section implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function getID(): string
	{
		/* ... */
	}


	public function getJSExpand(): string
	{
		/* ... */
	}


	public function getJSCollapse(): string
	{
		/* ... */
	}


	public function isCollapsed(): bool
	{
		/* ... */
	}


	public function appendContent(string $content): void
	{
		/* ... */
	}


	public function registerElement(UI_Form_Renderer_ElementFilter_RenderDef $renderDef): void
	{
		/* ... */
	}


	public function makeLast(): void
	{
		/* ... */
	}


	public function makeStandalone(): void
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function getFirstInvalid(): ?UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Sections/Section.php`

```php
namespace ;

class UI_Form_Renderer_Sections_Section implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function getID(): string
	{
		/* ... */
	}


	public function getJSExpand(): string
	{
		/* ... */
	}


	public function getJSCollapse(): string
	{
		/* ... */
	}


	public function isCollapsed(): bool
	{
		/* ... */
	}


	public function appendContent(string $content): void
	{
		/* ... */
	}


	public function registerElement(UI_Form_Renderer_ElementFilter_RenderDef $renderDef): void
	{
		/* ... */
	}


	public function makeLast(): void
	{
		/* ... */
	}


	public function makeStandalone(): void
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	public function isRequired(): bool
	{
		/* ... */
	}


	public function getFirstInvalid(): ?UI_Form_Renderer_ElementFilter_RenderDef
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Tabs.php`

```php
namespace ;

class UI_Form_Renderer_Tabs
{
	public function create(
		UI_Form_Renderer_ElementFilter_RenderDef $renderDef,
		UI_Form_Renderer_ElementFilter $elements,
	): UI_Form_Renderer_Tabs_Tab
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Tabs.php`

```php
namespace ;

class UI_Form_Renderer_Tabs
{
	public function create(
		UI_Form_Renderer_ElementFilter_RenderDef $renderDef,
		UI_Form_Renderer_ElementFilter $elements,
	): UI_Form_Renderer_Tabs_Tab
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Tabs/Tab.php`

```php
namespace ;

class UI_Form_Renderer_Tabs_Tab
{
	public function getLabel(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function renderContent(): string
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	public function renderLabel(): string
	{
		/* ... */
	}


	public function renderAbstract(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Renderer/Tabs/Tab.php`

```php
namespace ;

class UI_Form_Renderer_Tabs_Tab
{
	public function getLabel(): string
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function renderContent(): string
	{
		/* ... */
	}


	public function hasErrors(): bool
	{
		/* ... */
	}


	public function renderLabel(): string
	{
		/* ... */
	}


	public function renderAbstract(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Rule/Equals.php`

```php
namespace ;

/**
 * @category HTML
 * @package  HTML_QuickForm2
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Equals extends HTML_QuickForm2_Rule
{
}


```
###  Path: `/src/classes/UI/Form/Rule/Equals.php`

```php
namespace ;

/**
 * @category HTML
 * @package  HTML_QuickForm2
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Equals extends HTML_QuickForm2_Rule
{
}


```
###  Path: `/src/classes/UI/Form/Validator.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;

/**
 * Base class for validators.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Form_Validator
{
	/**
	 * @return string The type, e.g. "integer", "date"...
	 */
	abstract public function getDataType(): string;


	public function getForm(): UI_Form
	{
		/* ... */
	}


	/**
	 * @return mixed
	 */
	public function getValue()
	{
		/* ... */
	}


	public function getElement(): HTML_QuickForm2_Node
	{
		/* ... */
	}


	public function getRule(): HTML_QuickForm2_Rule_Callback
	{
		/* ... */
	}


	public function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * @param mixed $value
	 * @return bool
	 */
	public function validate($value): bool
	{
		/* ... */
	}


	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getFilteredValue($value): string
	{
		/* ... */
	}


	abstract public function getDefaultValue(): string;
}


```
###  Path: `/src/classes/UI/Form/Validator/Date.php`

```php
namespace ;

/**
 * Specialized validator class used for date input fields. Validates
 * the entry format, as well as the date itself.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleDate()
 */
class UI_Form_Validator_Date extends UI_Form_Validator
{
	public const ERROR_INVALID_DATE_ELEMENT_TYPE = 553001;

	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Date.php`

```php
namespace ;

/**
 * Specialized validator class used for date input fields. Validates
 * the entry format, as well as the date itself.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleDate()
 */
class UI_Form_Validator_Date extends UI_Form_Validator
{
	public const ERROR_INVALID_DATE_ELEMENT_TYPE = 553001;

	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Float.php`

```php
namespace ;

use AppUtils\RegexHelper as RegexHelper;
use UI\Form\FormException as FormException;

/**
 * Specialized validator class used for integer form elements:
 * used to validate values according to the format requirements
 * as well as minimum/maximum values if any.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleFloat()
 */
class UI_Form_Validator_Float extends UI_Form_Validator
{
	public const ERROR_INVALID_FLOAT_CONFIGURATION = 74801;

	public function getDefaultValue(): string
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Float.php`

```php
namespace ;

use AppUtils\RegexHelper as RegexHelper;
use UI\Form\FormException as FormException;

/**
 * Specialized validator class used for integer form elements:
 * used to validate values according to the format requirements
 * as well as minimum/maximum values if any.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleFloat()
 */
class UI_Form_Validator_Float extends UI_Form_Validator
{
	public const ERROR_INVALID_FLOAT_CONFIGURATION = 74801;

	public function getDefaultValue(): string
	{
		/* ... */
	}


	public function getDataType(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/ISODate.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

/**
 * Specialized validator class used for date input fields. Validates
 * the entry format, as well as the date itself.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleDate()
 */
class UI_Form_Validator_ISODate extends UI_Form_Validator
{
	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/ISODate.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;

/**
 * Specialized validator class used for date input fields. Validates
 * the entry format, as well as the date itself.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleDate()
 */
class UI_Form_Validator_ISODate extends UI_Form_Validator
{
	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Integer.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\RegexHelper as RegexHelper;

/**
 * Specialized validator class used for integer form elements:
 * used to validate values according to the format requirements
 * as well as minimum/maximum values if any.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleInteger()
 */
class UI_Form_Validator_Integer extends UI_Form_Validator
{
	public const ERROR_INVALID_CONFIGURATION = 74901;

	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Integer.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\RegexHelper as RegexHelper;

/**
 * Specialized validator class used for integer form elements:
 * used to validate values according to the format requirements
 * as well as minimum/maximum values if any.
 *
 * Also sets the data type so the type hints are displayed in
 * the UI for the field type.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRuleInteger()
 */
class UI_Form_Validator_Integer extends UI_Form_Validator
{
	public const ERROR_INVALID_CONFIGURATION = 74901;

	public function getDataType(): string
	{
		/* ... */
	}


	public function getDefaultValue(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Percent.php`

```php
namespace ;

/**
 * Specialized validator class used for percentage elements:
 * validating the value according to the min/max settings.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRulePercent()
 */
class UI_Form_Validator_Percent extends UI_Form_Validator_Float
{
	public function getDataType(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Form/Validator/Percent.php`

```php
namespace ;

/**
 * Specialized validator class used for percentage elements:
 * validating the value according to the min/max settings.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Form::addRulePercent()
 */
class UI_Form_Validator_Percent extends UI_Form_Validator_Float
{
	public function getDataType(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 146.43 KB
- **Lines**: 8225
File: `modules/ui/form/architecture-core.md`
