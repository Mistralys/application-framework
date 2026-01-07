<?php
/**
 * File containing the class {@see UI_Button}.
 *
 * @package User Interface
 * @subpackage UI Elements
 * @see UI_Button
 */

use AppUtils\Interfaces\StringableInterface;
use AppUtils\JSHelper;
use AppUtils\Traits\ClassableTrait;
use UI\AdminURLs\AdminURLInterface;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface;
use UI\Interfaces\ButtonLayoutInterface;
use UI\Traits\ActivatableTrait;
use UI\Traits\ButtonLayoutTrait;
use UI\Traits\ButtonSizeTrait;

/**
 * A configurable HTML `button` element. Use the
 * {@see UI::button()} method to instantiate a new
 * button instance.
 *
 * @package User Interface
 * @subpackage UI Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Button
    extends UI_BaseLockable
    implements
    UI_Renderable_Interface,
    UI_Interfaces_Button,
    ButtonGroupItemInterface
{
    use Application_Traits_Iconizable;
    use UI_Traits_RenderableGeneric;
    use ClassableTrait;
    use UI_Traits_Conditional;
    use UI_Traits_ClientConfirmable;
    use ButtonSizeTrait;
    use ActivatableTrait;
    use ButtonLayoutTrait;

    public const MODE_CLICKABLE = 'clickable';
    public const MODE_LINKED = 'linked';
    public const MODE_SUBMIT = 'submit';

    protected string $label = '';
    protected string $id;
    protected string $url = '';
    protected string $size = '';
    protected string $type = 'button';
    protected string $tooltipText = '';
    protected bool $disabled = false;
    protected string $disabledTooltip = '';
    private string $mode = '';
    private string $urlTarget = '';
    private string $javascript = '';
    private ?UI_Bootstrap_Popover $popover = null;
    private string $submitValue = '';

   /**
    * @var array<string,string>
    */
    protected array $attributes = array();

   /**
    * @var array<string,string>
    */
    protected array $styles = array();

    /**
     * @var array<string,string>
     */
    private array $dataAttributes = array();
    private bool $buttonLink = true;

    /**
     * @param string|StringableInterface|NULL $label
     */
    public function __construct($label=null)
    {
        $this->setLabel($label);

        $this->id = 'btn'.nextJSID();
    }

    public function getName() : string
    {
        return $this->getAttribute('name');
    }

    /**
     * Sets an attribute of the button tag.
     *
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $value
     * @return $this
     * @throws UI_Exception
     */
    public function setAttribute(string $name, $value) : self
    {
        $this->attributes[$name] = toString($value);
        return $this;
    }
    
   /**
    * Alias for {@setStyle()}.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function addStyle(string $name, $value) : self
    {
        return $this->setStyle($name, $value);
    }

    public function setLabel($label) : self
    {
        $this->label = toString($label);
        return $this;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setID(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return $this
     * @deprecated Not used anymore.
     */
    public function makeSpecial() : self
    {
        return $this->makeLayout('special');
    }
    
   /**
    * Styles the button as an informational button.
    *
    * @deprecated Use {@see self::makeInfo()} instead.
    * @return $this
    */
    public function makeInformational() : self
    {
        return $this->makeInfo();
    }


    
   /**
    * Turns the button into a submit button.
    * 
    * @param string $name
    * @param string|int|float|UI_Renderable_Interface $value
    * @return $this
    */
    public function makeSubmit(string $name, $value) : self
    {
        $this->mode = self::MODE_SUBMIT;
        $this->setName($name);
        $this->submitValue = (string)$value;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws UI_Exception
     */
    public function setName(string $name) : self
    {
        return $this->setAttribute('name', $name);
    }

   /**
    * Retrieves the button's ID attribute.
    * 
    * @return string
    */
    public function getID() : string
    {
        return $this->id;
    }
    
   /**
    * Sets a javascript statement to use as click handler of the button.
    * 
    * @param string $statement
    * @return $this
    */
    public function click(string $statement) : self
    {
        $this->mode = self::MODE_CLICKABLE;
        $this->javascript = $statement;

        return $this;
    }

    /**
     * Sets the title attribute of the button.
     *
     * @param string|number|UI_Renderable_Interface $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        return $this->setAttribute('title', toString($title));
    }

    /**
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($tooltip) : self
    {
        $this->tooltipText = toString($tooltip);
        return $this;
    }

    public function getTooltip() : string
    {
        return $this->tooltipText;
    }

    /**
    * Sets the tooltip text, to enable the button tooltip.
    *
    * @deprecated Use {@see self::setTooltip()} instead.
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setTooltipText($text) : self
    {
        return $this->setTooltip($text);
    }
    
   /**
    * Styles the button like a regular link (but keeping the button size).
    *
    * @param bool $buttonLink Use the `btn-link` class? Otherwise, it will be a regular link tag.
    * @return $this
    */
    public function makeLink(bool $buttonLink=true) : self
    {
        $this->buttonLink = $buttonLink;

        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_LINK);
    }
    
   /**
    * Sets the text to display on the button when it is 
    * switched to the loading state. Note that the loading
    * state can only be triggered clientside, however.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setLoadingText($text) : self
    {
        return $this->setAttribute('data-loading-text', $text);
    }
    
    public function __toString()
    {
        return $this->render();
    }

    public function getType() : string
    {
        if($this->mode === self::MODE_SUBMIT)
        {
            return 'submit';
        }

        return 'button';
    }

    /**
     * @return array<string,string>
     * @throws Application_Exception
     */
    protected function getAttributes() : array
    {
        if(isset($this->popover))
        {
            $this->popover->setAttachToID($this->getID());
            $this->click($this->popover->getToggleStatement());
            $this->popover->render();
        }

        $attribs = array_merge($this->dataAttributes, $this->attributes);

        $attribs['id'] = $this->id;
        $attribs['type'] = $this->getType();
        $attribs['autocomplete'] = 'off'; // avoid firefox autocompletion bug
    
        $attribs['class'] = implode(' ', $this->resolveClasses());
    
        if(!empty($this->styles)) {
            $attribs['style'] = compileStyles($this->styles);
        }
        
        $title = '';
        if(isset($this->title)) {
            $title = $this->title;
        }
    
        if(isset($this->tooltipText)) 
        {
            $tooltip = null;
            
            if($this->disabled && $this->disabledTooltip) {
                $tooltip = $this->disabledTooltip;
            } else if(!$this->locked) {
                $tooltip = $this->tooltipText;
            }
            
            if($tooltip) {
                $title = $tooltip;
                \JSHelper::tooltipify($this->id);
            }
        }
    
        if(!empty($title)) {
            $attribs['title'] = $title;
        }

        if($this->confirmMessage !== null)
        {
            $attribs['onclick'] = $this->confirmMessage->getJavaScript();
        }
        else
        {
            switch ($this->mode)
            {
                case self::MODE_LINKED:
                    $attribs['href'] = $this->getURL();
                    $attribs['target'] = $this->urlTarget;
                    break;

                case self::MODE_CLICKABLE:
                    $attribs['onclick'] = $this->getJavaScript();
                    break;

                case self::MODE_SUBMIT:
                    $attribs['value'] = $this->submitValue;
                    break;
            }
        }

        if($this->locked) 
        {
            $attribs['onclick'] = "LockManager.DialogActionDisabled()";
        } 
        else if($this->disabled) 
        {
            unset(
                $attribs['onclick'],
                $attribs['href'],
                $attribs['type'],
                $attribs['target']
            );
        }
        
        return $attribs;
    }

    /**
     * @return string[]
     */
    private function resolveClasses() : array
    {
        $classes = $this->classes;

        if($this->layout !== ButtonLayoutInterface::LAYOUT_LINK || $this->buttonLink === true)
        {
            $classes[] = 'btn-'.$this->resolveLayout();
            $classes[] = 'btn';
        }

        $sizeClass = $this->getSizeClass();
        if(!empty($sizeClass))
        {
            $classes[] = $sizeClass;
        }

        if($this->isActive()) {
            $classes[] = 'active';
        }

        if($this->locked) {
            $this->disabled = true;
            $classes[] = 'btn-locked';
        }

        if($this->disabled) {
            $classes[] = 'disabled';
        }

        return $classes;
    }

   /**
    * Ensures that the text in the button does not wrap to the next line.
    * 
    * @return $this
    */
    public function setNowrap() : self
    {
        $this->addClass('text-nowrap');
        return $this;
    }
    
    public function render() : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $atts = $this->getAttributes();
        $tokens = array();
        
        foreach($atts as $name => $value) {
            $tokens[] = $name.'="'.$value.'"';
        }
        
        $label = $this->label;
        if(isset($this->icon)) { 
            $label = $this->icon->render().' '.$label;
        }
        
        $tag = 'button';
        if(!empty($this->url)) 
        {
            $tag = 'a';
        }
        
        $html = 
        '<'.$tag.' '.implode(' ', $tokens).'>'.
            $label.
        '</'.$tag.'>';
        
        $this->getUI()->addJavascriptOnload(sprintf(
            'UI.Handle_RegisterServerButton(%s, %s, %s)', 
            JSHelper::phpVariable2JS($this->getID()),
            JSHelper::phpVariable2JS($this->layout),
            JSHelper::phpVariable2JS($this->type)
        ));
        
        return $html;
    }
    
    public function display() : void
    {
        echo $this->render();
    }

    /**
     * @param string|AdminURLInterface $url
     * @param string $target
     * @return $this
     */
    public function link($url, string $target='') : self
    {
        $this->url = (string)$url;
        $this->urlTarget = $target;
        $this->mode = self::MODE_LINKED;
        
        return $this;
    }
    
   /**
    * Sets the button as a block element that will fill 
    * all the available horizontal space.
    * 
    * @return $this
    */
    public function makeBlock() : self
    {
        $this->addClass('btn-block');
        return $this;
    }
    
   /**
    * Sets a style for the main body tag's <code>style</code> attribute.
    * 
    * @param string $style The style to set, e.g. <code>padding-top</code>
    * @param mixed $value The value to set the style to. 
    * @return $this
    */
    public function setStyle(string $style, $value) : self
    {
        $this->styles[$style] = (string)$value;
        return $this;
    }
    
   /**
    * Enables the button's "pushed" state.
    * 
    * @return $this
    */
    public function push() : self
    {
        return $this->makeActive();
    }
    
   /**
    * Removes the button's "pushed" state.
    * 
    * @return $this
    */
    public function unpush() : self
    {
        return $this->makeActive(false);
    }
    
   /**
    * Makes the button redirect to the target URL, displaying
    * a clientside loader while the target page loads. 
    * 
    * @param string|AdminURLInterface $url
    * @param string $loaderText
    * @return $this
    */
    public function loaderRedirect($url, string $loaderText='') : self
    {
        return $this->click(sprintf(
            "application.redirect('%s', %s)",
            $url,
            JSHelper::phpVariable2JS($loaderText, JSHelper::QUOTE_STYLE_SINGLE)
        ));
    }

    /**
     * Makes the button disabled.
     *
     * @param string|number|UI_Renderable_Interface|NULL $reason
     * @return $this
     * @throws UI_Exception
     */
    public function disable($reason='') : self
    {
        $this->disabled = true;
        $this->disabledTooltip = toString($reason);
        
        return $this;
    }

    public function isDisabled() : bool
    {
        return $this->disabled;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function isClickable() : bool
    {
        return $this->mode === self::MODE_CLICKABLE;
    }

    public function isLinked() : bool
    {
        return $this->mode === self::MODE_LINKED;
    }

    public function isSubmittable() : bool
    {
        return $this->mode === self::MODE_SUBMIT;
    }

    public function getJavascript() : string
    {
        return $this->javascript;
    }

    public function isDangerous() : bool
    {
        return $this->layout === ButtonLayoutInterface::LAYOUT_DANGER;
    }

    private function getAttribute(string $name) : string
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return '';
    }

    /**
     * Makes the button use an existing popover instance.
     * It will be reconfigured to be used with the button.
     *
     * @param UI_Bootstrap_Popover $popover
     * @return $this
     */
    public function setPopover(UI_Bootstrap_Popover $popover) : self
    {
        $this->popover = $popover;
        return $this;
    }

    /**
     * Makes the button display a popover.
     *
     * NOTE: Button will only handle the popover.
     * Setting a click handler or link will be ignored.
     *
     * @return UI_Button
     * @throws UI_Exception
     */
    public function makePopover() : self
    {
        $this->getPopover();
        return $this;
    }

    /**
     * Retrieves the button's popover instance to
     * configure it. Automatically makes the button
     * a popover button.
     *
     * @return UI_Bootstrap_Popover
     * @throws UI_Exception
     */
    public function getPopover() : UI_Bootstrap_Popover
    {
        if(!isset($this->popover))
        {
            $this->popover = UI::popover('');
        }

        return $this->popover;
    }

    public function addDataAttribute(string $name, string $value) : self
    {
        $this->dataAttributes['data-'.$name] = $value;
        return $this;
    }

    /**
     * @param string|AdminURLInterface $url
     * @param string $target
     * @return self
     */
    public function presetView($url, string $target='') : self
    {
        return $this
            ->setLabel(t('View'))
            ->setIcon(UI::icon()->view())
            ->link($url, $target);
    }
}
