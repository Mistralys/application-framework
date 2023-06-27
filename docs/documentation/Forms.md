# Forms

## Introduction

The application framework uses the package `HTML_QuickForm2` to handle forms. This in turn
is wrapped in what is called a _Formable_, a utility class that handles all the application's
specifics around the forms package.

> Note: The fork https://github.com/Mistralys/HTML_QuickForm2 is used in the framework, as it
has a number of improvements regarding performance when using many forms in parallel, as well
as several quality of life improvements.

No layouting work needs to be done: all forms are standardized for a streamlined user
experience. The framework offers methods for all relevant aspects:

- The layout.
- All commonly used data types.
- Custom data types can be added in the application itself.
- Inline documentation.
- Input validation.

## Formables

The framework has a "Formable" interface and base class, which bring together all aspects of
form building. This includes methods for creating input elements, as well as for adding
validation. This system has the `HTML_QuickForm2` at its core, but simplified for use in the
framework.

Administration screens implement the formable interface, so they can be used to handle a
single form. If this is not sufficient, any number of forms can be created on the fly.
All examples here in the documentation assume that you are working with a single form in
an admin screen.

## Typical admin screen form

The following example shows the typical structure for handling a form in a screen:

```php
class Documentation_Form_TypicalStructure extends Application_Admin_Area_Mode
{
    protected function _handleActions()
    {
        // First off, create the form instance.
        $this->createSettingsForm();
        
        // The form is considered valid if it has been submitted,
        // and all elements passed validation.
        if($this->isFormValid())
        {
            $this->handleFormSubmitted($this->getFormValues());
        }
    }
    
    protected function _renderContent()
    {
        // Append the form to the renderer: this will use
        // the form as content of the page.
        return $this->renderer
            ->setTitle(t('Example form'))
            ->appendFormable($this)
            ->makeWithSidebar();
    }
    
    protected function _handleSidebar()
    {
        // A clickable submit button is configured to 
        // submit the formable when it is, well, clicked.
        $this->sidebar->addButton('save', t('Submit now'))
        ->setIcon(UI::icon()->save())
        ->makeClickableSubmit($this);
        
        $this->sidebar->addButton('cancel', t('Cancel'))
        ->makeLinked('(relevant cancel url)');
    }
    
    /**
     * Called when the form has been submitted and is valid.
     * @param array<string,string> $formValues
     */
    protected function handleFormSubmitted(array $formValues) : void
    {
        // do something with the values
        
        // Redirect to a relevant page after the operation, 
        // with a success message.
        $this->redirectWithSuccessMessage(
            t('The form was submitted successfully.'),
            '(redirect url)'
        );
    }
    
    /**
     * Creates and configures the form. 
     */
    protected function createSettingsForm() : void
    {
        $defaultValues = array( 
            'name' => ''
        );
        
        $this->createFormableForm('form_name', $defaultValues);
        
        $el = $this->addElementText('name', t('Name'));
        $el->addFilterTrim();
    }
}
```

## Configuring validation

The Quickform package works with "rules": A form element can have any number of validation
rules attached to it, which are verified in the order they are added to the element. If
all rules return true, the element is considered valid.

### Adding rules

The framework comes with a range of predefined validation methods, which can be freely
combined to get the required result.

```php
class Documentation_Form_AddingRules extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->addRuleLabel($el); // Only allow characters for item labels
        $this->makeLengthLimited($el, 0, 80); // Limit length to a maximum of 80 characters
    }
}
```

### Rules and empty elements

Rules must always return true if the element's value is empty: the required rule is
responsible for validating this case. Empty non-required elements must be considered
valid.

### Available validation rules

The following format-specific methods are available for formables:

- `addRuleNameOrTitle()` - Alphanumerical, with a limited set of punctuation allowed.
- `addRuleAlias()` - Element alias; lowercase alphanumerical without spaces.
- `addRuleEmail()` - Validate an email address.
- `addRuleFilename()` - Rule for specifying a file name, with common restrictions.
- `addRuleFloat()` - Number with dot or comma for decimals.
- `addRuleInteger()` - Integer number.
- `addRuleISODate()` - Date in the format 2021-02-25.
- `addRuleNoHTML()` - Disallow the use of HTML tags.
- `addRulePhone()` - For entering phone numbers.

Custom validation methods:

- `addRuleCallback()` - Use a callback function/method for validation.
- `addRuleRegex()` - Validate using a regular expression.

## Marking elements as structural

This is typically used in conjunction with data types that have states, like products:
It means that changing the value will change the state of the record. These elements
receive a visual distinction in the UI to identify them as structural.

```php
class Documentation_Form_StructuralElement extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->makeStructural($el);
    }
}
```

## Adding inline documentation

As shown in the "Configuring validation" chapter, validation rules automatically add
documentation on the expected input format. In addition to this, every form element
can be described further with the `setComments()` method.

It is recommended to always add background information on what the form element's data
is used for, if this is not entirely obvious. Examples are also useful if applicable,
so that new users do not have to guess what's expected.

```php
class Documentation_Form_InlineDocumentation extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        $el->setComment((string)sb()
            ->t('With the string builder, it is easy to add detailed information.')
            ->nl()
            ->icon(UI::icon()->information()->makeInformation())
            ->info(t('Some important tips here.'))
        );
    }
}
```

## Element size classes

To give elements a specific size, you may use the element's `addClass()` method
with one of the available size classes:

- `input-mini`
- `input-small`
- `input-medium`
- `input-large`
- `input-xlarge`
- `input-xxlarge`

## Marking elements as required

Marking elements as required is done via the formable's `makeRequired()` method.

```php
class Documentation_Form_RequiredElements extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        
        $this->makeRequired($el);
    }
}
```

## Form elements

### Text input

```php
class Documentation_Form_Element_TextInput extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        $el = $this->addElementText('name', t('Name'));
        $el->addClass('input-xxlarge');
        $el->addFilterTrim();
        $el->setComment(t('Some comments here.'));
    }
}
```

### Select

This adds a traditional select element, with or without grouping.

```php
class Documentation_Form_Element_Select extends Application_Admin_Area_Mode
{
    protected function createSettingsForm() : void
    {
        $this->createFormableForm('form_name');
        
        // Add a simple flat select without option groups
        $ungrouped = $this->addElementSelect('name', t('Name'));
        $ungrouped->addOption(t('Please select...'), '');
        $ungrouped->addOption(t('Option 1'), '1');
        $ungrouped->addOption(t('Option 2'), '2');
        
        // Add a select with option groups
        $grouped = $this->addElementSelect('name', t('Name'));
        $grouped->addOption(t('Please select...'), '');
        
        $groupA = $grouped->addOptgroup(t('Group A'));
        $groupA->addOption(t('Option 1'), '1');
        $groupA->addOption(t('Option 2'), '2');

        $groupB = $grouped->addOptgroup(t('Group B'));
        $groupB->addOption(t('Option 3'), '3');
        $groupB->addOption(t('Option 4'), '4');
    }
}
```
