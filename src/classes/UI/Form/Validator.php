<?php
/**
 * File containing the {@link UI_Form_Validator} class.
 *
 * @package Application
 * @subpackage Forms
 * @see UI_Form_Validator
 */

declare(strict_types=1);

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
     * @var HTML_QuickForm2_Element
     */
    protected $element;

    /**
     * @var UI_Form
     */
    protected $form;

    /**
     * @var HTML_QuickForm2_Rule_Callback
     */
    protected $rule;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param UI_Form $form
     * @param HTML_QuickForm2_Element $element
     * @throws HTML_QuickForm2_InvalidArgumentException
     * @throws HTML_QuickForm2_NotFoundException
     */
    public function __construct(UI_Form $form, HTML_QuickForm2_Element $element)
    {
        $this->form = $form;
        $this->element = $element;
        $this->element->setRuntimeProperty('validator', $this);
        $this->element->setAttribute('data-type', $this->getDataType());
        $this->element->addFilter(array($this, 'getFilteredValue'));
        $this->rule = $this->form->addRuleCallback($this->element, array($this, 'validate'), '');

        $this->checkConfig();
    }

    /**
     * @return string The type, e.g. "integer", "date"...
     */
    abstract public function getDataType() : string;

    abstract protected function checkConfig() : void;

    public function getForm() : UI_Form
    {
        return $this->form;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getElement() : HTML_QuickForm2_Element
    {
        return $this->element;
    }

    public function getRule() : HTML_QuickForm2_Rule_Callback
    {
        return $this->rule;
    }

    protected function makeError(string $message) : bool
    {
        $this->rule->setMessage($message);
        return false;
    }

    public function getErrorMessage() : string
    {
        return strval($this->rule->getMessage());
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value) : bool
    {
        $this->value = $this->applyFilters($value);

        if(empty($this->value))
        {
            return true;
        }

        return $this->_validate();
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getFilteredValue($value) : string
    {
        if(empty($value) || !$this->validate($value))
        {
            return strval($value);
        }

        return $this->applyFilters($value);
    }

    abstract public function getDefaultValue() : string;

    /**
     * @param mixed $value
     * @return string
     */
    abstract protected function applyFilters($value) : string;

    /**
     * Validates the value: will not be triggered if empty, since that
     * should be handled by a required rule.
     *
     * @return boolean
     */
    abstract protected function _validate() : bool;
}
