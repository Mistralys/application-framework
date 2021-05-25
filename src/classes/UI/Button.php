<?php

use AppUtils\Traits_Classable;

class UI_Button extends UI_BaseLockable implements UI_Interfaces_Button, UI_Interfaces_Conditional
{
    use Application_Traits_Iconizable;
    use UI_Traits_RenderableGeneric;
    use Traits_Classable;
    use UI_Traits_Conditional;
    use UI_Traits_ClientConfirmable;
 
    const ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION = 66601;
    const ERROR_UNKNOWN_BOOTSTRAP_SIZE = 66602;

    const MODE_CLICKABLE = 'clickable';
    const MODE_LINKED = 'linked';

    /**
    * @var string
    */
    protected $label = '';
    
   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var array<string,string>
    */
    protected $attributes = array();

   /**
    * @var array<string,string>
    */
    protected $styles = array();
    
   /**
    * @var string
    */
    protected $url = '';
    
   /**
    * @var string
    */
    protected $size = '';
    
   /**
    * @var string
    */
    protected $layout = 'default';
    
   /**
    * @var string
    */
    protected $type = 'button';
    
   /**
    * @var string
    */
    protected $tooltipText = '';
    
   /**
    * @var array<int,array<string,string>>
    */
    protected $sizes = array(
        2 => array(
            'large' => 'large',
            'small' => 'small',
            'mini' => 'mini'
        ),
        4 => array(
            'large' => 'lg',
            'small' => 'sm',
            'mini' => 'xs'
        )
    );
    
   /**
    * @var boolean
    */
    protected $disabled = false;
    
   /**
    * @var string
    */
    protected $disabledTooltip = '';

    /**
     * @var string
     */
    private $mode = '';

    /**
     * @var string
     */
    private $urlTarget = '';

    /**
     * @var string
     */
    private $javascript = '';

    public function __construct($label='')
    {
        $this->setLabel($label);

        $this->id = 'btn'.nextJSID();
    }

   /**
    * Sets an attribute of the button tag.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function setAttribute(string $name, $value) : UI_Button
    {
        $this->attributes[$name] = strval($value);
        return $this;
    }
    
   /**
    * Alias for {@setStyle()}.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function addStyle(string $name, $value)
    {
        return $this->setStyle($name, $value);
    }

    public function setLabel(string $label) : UI_Button
    {
        $this->label = $label;
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
    public function setID(string $id) : UI_Button
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
    public function makeSmall() : UI_Button
    {
        return $this->makeSize('small');
    }

    /**
     * Makes the button into a large button.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function makeLarge() : UI_Button
    {
        return $this->makeSize('large');
    }

    /**
     * Makes the button into a miniature button.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function makeMini() : UI_Button
    {
        return $this->makeSize('mini');
    }

    /**
     * @param string $size
     * @return $this
     * @throws Application_Exception
     */
    public function makeSize(string $size) : UI_Button
    {
        $version = $this->getUI()->getBoostrapVersion();
        
        if(!isset($this->sizes[$version]))
        {
            throw new Application_Exception(
                'Unknown bootstrap version',
                sprintf(
                    'No button sizes known for bootstrap version [%s].',
                    $version
                ),
                self::ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION
            );
        }
        
        if(!isset($this->sizes[$version][$size]))
        {
            throw new Application_Exception(
                'Unknown button size',
                sprintf(
                    'Button size [%s] not known for bootstrap version [%s].',
                    $size,
                    $version
                ),
                self::ERROR_UNKNOWN_BOOTSTRAP_SIZE
            );
        }
        
        $this->size = $this->sizes[$version][$size];
        
        return $this;
    }
    
   /**
    * Styles the button as a primary button.
    * 
    * @return $this
    */
    public function makePrimary() : UI_Button
    {
        return $this->makeType('primary');
    }

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous() : UI_Button
    {
        return $this->makeType('danger');
    }
    
   /**
    * Styles the button for developers.
    * 
    * @return $this
    */
    public function makeDeveloper() : UI_Button
    {
        return $this->makeType('developer');
    }
    
    public function makeSpecial() : UI_Button
    {
        return $this->makeType('special'); 
    }
    
   /**
    * Styles the button as an informational button.
    *
    * @deprecated
    * @return $this
    */
    public function makeInformational() : UI_Button
    {
        return $this->makeInfo();
    }

    /**
     * @return $this
     */
    public function makeInfo() : UI_Button
    {
        return $this->makeType('info');
    }

    /**
    * Styles the button as a success button.
    * 
    * @return $this
    */
    public function makeSuccess() : UI_Button
    {
        return $this->makeType('success');
    }
    
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @return $this
    */
    public function makeWarning() : UI_Button
    {
        return $this->makeType('warning');
    }
    
   /**
    * Styles the button as an inverted button.
    * 
    * @return $this
    */
    public function makeInverse() : UI_Button
    {
        return $this->makeType('inverse');
    }
    
   /**
    * Sets the button's layout to the specified type.
    * 
    * @param string $type
    * @return $this
    */
    protected function makeType(string $type) : UI_Button
    {
        $this->layout = $type;
        
        return $this;
    }
    
   /**
    * Turns the button into a submit button.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function makeSubmit(string $name, $value) : UI_Button
    {
        $this->type = 'submit';
        $this->setAttribute('name', $name);
        $this->setAttribute('value', strval($value));
        
        return $this;
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
    public function click(string $statement) : UI_Button
    {
        $this->mode = self::MODE_CLICKABLE;
        $this->javascript = $statement;

        return $this;
    }

    /**
     * Sets the title attribute of the button.
     *
     * @param scalar|UI_Renderable_Interface $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : UI_Button
    {
        return $this->setAttribute('title', toString($title));
    }

    /**
     * @param scalar|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($tooltip)
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
    * @deprecated
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setTooltipText($text) : UI_Button
    {
        return $this->setTooltip($text);
    }
    
   /**
    * Styles the button like a regular link (but keeping the button size).
    * 
    * @return $this
    */
    public function makeLink() : UI_Button
    {
        return $this->makeType('link');
    }
    
   /**
    * Sets the text to display on the button when it is 
    * switched to the loading state. Note that the loading
    * state can only be triggered clientside however.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setLoadingText($text) : UI_Button
    {
        return $this->setAttribute('data-loading-text', $text);
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
   /**
    * @return array<string,string>
    */
    protected function getAttributes() : array
    {
        $atts = $this->attributes;
    
        $atts['id'] = $this->id;
        $atts['type'] = $this->type;
        $atts['autocomplete'] = 'off'; // avoid firefox autocompletion bug
    
        $classes = $this->classes;
        $classes[] = 'btn';
        $classes[] = 'btn-'.$this->layout;
    
        if(!empty($this->size)) 
        {
            $classes[] = 'btn-'.$this->size;
        }
        
        if($this->locked) {
            $this->disabled = true;
            $classes[] = 'btn-locked';
        }
    
        if($this->disabled) {
            $classes[] = 'disabled';
        }
        
        $atts['class'] = implode(' ', $classes);
    
        if(!empty($this->styles)) {
            $atts['style'] = compileStyles($this->styles);
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
                JSHelper::tooltipify($this->id);
            }
        }
    
        if(!empty($title)) {
            $atts['title'] = $title;
        }

        if($this->confirmMessage !== null)
        {
            $atts['onclick'] = $this->confirmMessage->getJavaScript();
        }
        else
        {
            switch ($this->mode)
            {
                case self::MODE_LINKED:
                    $atts['href'] = $this->getURL();
                    $atts['target'] = $this->urlTarget;
                    break;

                case self::MODE_CLICKABLE:
                    $atts['onclick'] = $this->getJavascript();
                    break;
            }
        }
        
        if($this->locked) 
        {
            $atts['onclick'] = "LockManager.DialogActionDisabled()";
        } 
        else if($this->disabled) 
        {
            unset($atts['onclick']);
            unset($atts['href']);
            unset($atts['type']);
            unset($atts['target']);
        }
        
        return $atts;
    }
    
   /**
    * Ensures that the text in the button does not wrap to the next line.
    * 
    * @return $this
    */
    public function setNowrap() : UI_Button
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
    
    public function link(string $url, string $target='') : UI_Button
    {
        $this->url = $url;
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
    public function makeBlock() : UI_Button
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
    public function setStyle(string $style, $value) : UI_Button
    {
        $this->styles[$style] = strval($value);
        return $this;
    }
    
   /**
    * Enables the button's "pushed" state.
    * 
    * @return $this
    */
    public function push() : UI_Button
    {
        return $this->addClass('active');
    }
    
   /**
    * Removes the button's "pushed" state.
    * 
    * @return $this
    */
    public function unpush() : UI_Button
    {
        return $this->removeClass('active');
    }
    
   /**
    * Makes the button redirect to the target URL, displaying
    * a clientside loader while the target page loads. 
    * 
    * @param string $url
    * @param string $loaderText
    * @return $this
    */
    public function loaderRedirect(string $url, string $loaderText='') : UI_Button
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
    * @param string $helpText
    * @return $this
    */
    public function disable(string $helpText='') : UI_Button
    {
        $this->disabled = true;
        $this->disabledTooltip = $helpText;
        
        return $this;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function isClickable() : bool
    {
        $js = $this->getJavascript();
        return !empty($js);
    }

    public function isLinked() : bool
    {
        return !empty($this->url);
    }

    public function getJavascript() : string
    {
        return $this->getAttribute('onclick');
    }

    public function isDangerous() : bool
    {
        return $this->layout === 'danger';
    }

    private function getAttribute(string $name) : string
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return '';
    }
}
