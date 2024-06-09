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
    UI_Interfaces_Button
{
    use Application_Traits_Iconizable;
    use UI_Traits_RenderableGeneric;
    use ClassableTrait;
    use UI_Traits_Conditional;
    use UI_Traits_ClientConfirmable;
 
    public const ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION = 66601;
    public const ERROR_UNKNOWN_BOOTSTRAP_SIZE = 66602;

    public const MODE_CLICKABLE = 'clickable';
    public const MODE_LINKED = 'linked';
    public const MODE_SUBMIT = 'submit';

    public const SIZE_SMALL = 'small';
    public const SIZE_LARGE = 'large';
    public const SIZE_MINI = 'mini';
    const TYPE_LINK = 'link';

    protected string $label = '';
    protected string $id;
    protected string $url = '';
    protected string $size = '';
    protected string $layout = 'default';
    protected string $type = 'button';
    protected string $tooltipText = '';
    protected bool $disabled = false;
    protected string $disabledTooltip = '';
    private string $mode = '';
    private string $urlTarget = '';
    private string $javascript = '';
    private ?UI_Bootstrap_Popover $popover = null;
    private bool $active = false;
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
    * @var array<int,array<string,string>>
    */
    protected static array $sizes = array(
        2 => array(
            self::SIZE_LARGE => 'large',
            self::SIZE_SMALL => 'small',
            self::SIZE_MINI => 'mini'
        ),
        4 => array(
            self::SIZE_LARGE => 'lg',
            self::SIZE_SMALL => 'sm',
            self::SIZE_MINI => 'xs'
        )
    );
    
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
     * Makes the button into a small button.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function makeSmall() : self
    {
        return $this->makeSize(self::SIZE_SMALL);
    }

    /**
     * Makes the button into a large button.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function makeLarge() : self
    {
        return $this->makeSize(self::SIZE_LARGE);
    }

    /**
     * Makes the button into a miniature button.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function makeMini() : self
    {
        return $this->makeSize(self::SIZE_MINI);
    }

    /**
     * @param string $size
     * @return $this
     * @throws Application_Exception
     */
    public function makeSize(string $size) : self
    {
        self::requireValidSize($size);
        
        $this->size = self::$sizes[UI::getInstance()->getBoostrapVersion()][$size];
        
        return $this;
    }

    /**
     * @param string $size
     * @throws UI_Exception
     */
    public static function requireValidSize(string $size) : void
    {
        $version = UI::getInstance()->getBoostrapVersion();

        if(!isset(self::$sizes[$version]))
        {
            throw new UI_Exception(
                'Unknown bootstrap version',
                sprintf(
                    'No button sizes known for bootstrap version [%s].',
                    $version
                ),
                self::ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION
            );
        }

        if(isset(self::$sizes[$version][$size]))
        {
            return;
        }

        throw new UI_Exception(
            'Unknown button size',
            sprintf(
                'Button size [%s] not known for bootstrap version [%s].',
                $size,
                $version
            ),
            self::ERROR_UNKNOWN_BOOTSTRAP_SIZE
        );
    }
    
   /**
    * Styles the button as a primary button.
    * 
    * @return $this
    */
    public function makePrimary() : self
    {
        return $this->makeType('primary');
    }

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous() : self
    {
        return $this->makeType('danger');
    }
    
   /**
    * Styles the button for developers.
    * 
    * @return $this
    */
    public function makeDeveloper() : self
    {
        return $this->makeType('developer');
    }
    
    public function makeSpecial() : self
    {
        return $this->makeType('special'); 
    }
    
   /**
    * Styles the button as an informational button.
    *
    * @deprecated
    * @return $this
    */
    public function makeInformational() : self
    {
        return $this->makeInfo();
    }

    /**
     * @return $this
     */
    public function makeInfo() : self
    {
        return $this->makeType('info');
    }

    /**
    * Styles the button as a success button.
    * 
    * @return $this
    */
    public function makeSuccess() : self
    {
        return $this->makeType('success');
    }
    
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @return $this
    */
    public function makeWarning() : self
    {
        return $this->makeType('warning');
    }
    
   /**
    * Styles the button as an inverted button.
    * 
    * @return $this
    */
    public function makeInverse() : self
    {
        return $this->makeType('inverse');
    }
    
   /**
    * Sets the button's layout to the specified type.
    * 
    * @param string $type
    * @return $this
    */
    protected function makeType(string $type) : self
    {
        $this->layout = $type;
        
        return $this;
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

        return $this->makeType(self::TYPE_LINK);
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
        $classes[] = 'btn';
        $classes[] = 'btn-'.$this->layout;

        if(!empty($this->size))
        {
            $classes[] = 'btn-'.$this->size;
        }

        if($this->active)
        {
            $classes[] = 'active';
        }

        if($this->locked) {
            $this->disabled = true;
            $classes[] = 'btn-locked';
        }

        if($this->disabled) {
            $classes[] = 'disabled';
        }

        if($this->layout === self::TYPE_LINK && !$this->buttonLink)
        {
            $keep = array();
            foreach($classes as $class) {
                if(strpos($class, 'btn') !== 0) {
                    $keep[] = $class;
                }
            }

            $classes = $keep;
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
        return $this->addClass('active');
    }
    
   /**
    * Removes the button's "pushed" state.
    * 
    * @return $this
    */
    public function unpush() : self
    {
        return $this->removeClass('active');
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
        return $this->layout === 'danger';
    }

    public function makeActive(bool $active=true) : self
    {
        $this->active = $active;
        return $this;
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
