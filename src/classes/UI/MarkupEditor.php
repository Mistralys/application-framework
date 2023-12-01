<?php
/**
 * File containing the {@link UI_MarkupEditor} class.
 * @package Application
 * @subpackage MarkupEditor
 * @see UI_MarkupEditor
 */

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\OptionableTrait;

/**
 * Base class for the available markup editors.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
abstract class UI_MarkupEditor implements OptionableInterface, StringableInterface
{
    use OptionableTrait;
    
    protected UI $ui;
    protected string $jsID;
    protected bool $started = false;
    protected HTML_QuickForm2_Element $element;
    protected string $selector;
    protected Application_Countries_Country $country;
    
    public function __construct(UI $ui, HTML_QuickForm2_Element $element, Application_Countries_Country $country)
    {
        $this->ui = $ui;
        $this->jsID = 'me'.nextJSID();
        $this->element = $element;
        $this->selector = '#'.$element->getId();
        $this->country = $country;
    }
    
    /**
     * Starts the redactor: adds the required javascript includes and stylesheets,
     * as well as any configuration statements as needed. This is called automatically
     * by the {@link UI::renderHeadIncludes()} method.
     *
     * @return UI_MarkupEditor
     */
    public function start() : UI_MarkupEditor 
    {
        if($this->started) {
            return $this;
        }
        
        $this->started = true;
        
        $this->injectJS();
        
        $this->_start();
        
        return $this;
    }
    
    abstract public static function getLabel() : string;
    
    abstract public function injectControlMarkup(UI_Form_Renderer_Element $element, string $markup) : string;
    
    abstract protected function injectJS();
    
    abstract protected function _start();

    /**
     * To make it compatible with form attributes.
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
