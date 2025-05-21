<?php
/**
 * File containing the {@see UI_Page_Sidebar_Item_Button} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Sidebar_Item_Button
 */

use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\Traits\ClassableTrait;
use UI\AdminURLs\AdminURLInterface;
use UI\Interfaces\ActivatableInterface;
use UI\Interfaces\ButtonLayoutInterface;
use UI\Traits\ButtonLayoutTrait;
use function AppUtils\parseVariable;

/**
 * A single button in the sidebar.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_sidebar_button
 */
class UI_Page_Sidebar_Item_Button
    extends UI_Page_Sidebar_LockableItem
    implements UI_Interfaces_Button
{
    public const ERROR_CANNOT_DETERMINE_FORM_NAME = 55301;

    public const STATE_DISABLED = 'disabled';
    public const STATE_ENABLED = 'enabled';

    public const MODE_SUBMIT = 'submit';
    public const MODE_NONE = 'none';
    public const MODE_LINKED = 'linked';
    public const MODE_CLICKABLE = 'clickable';

    use Application_Traits_Iconizable;
    use ClassableTrait;
    use UI_Traits_ClientConfirmable;
    use ButtonLayoutTrait;

    protected string $title = '';
    protected string $name;
    protected string $mode = self::MODE_NONE;
    protected string $url = '';
    protected string $javascript = '';
    protected string $state = 'enabled';
    protected string $style = 'normal';
    protected string $onclick = '';
    protected string $id;
    protected string $formName = '';
    protected string $disabledTooltip = '';
    protected ?string $urlTarget = null;
    protected string $tooltip = '';
    protected string $loadingText = '';

    /**
     * @param UI_Page_Sidebar $sidebar
     * @param string $name
     * @param string|UI_Renderable_Interface|int|float $title
     */
    public function __construct(UI_Page_Sidebar $sidebar, string $name, $title = '')
    {
        parent::__construct($sidebar);

        $this->name = $name;
        $this->id = 'button_' . $this->name;

        $this->setLabel($title);
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|AdminURLInterface $url
     * @param string $target
     * @return $this
     * @see UI_Page_Sidebar_Item_Button::makeLinked()
     */
    public function link($url, string $target = '') : self
    {
        return $this->makeLinked($url, !empty($target));
    }

    /**
     * Makes the button link to the specified URL.
     *
     * @param string|AdminURLInterface|array<string,string> $urlOrParams
     * @param boolean $newWindow Whether to open the link in a new tab/window
     * @return $this
     * @throws BaseClassHelperException
     */
    public function makeLinked($urlOrParams, bool $newWindow=false) : self
    {
        if (is_array($urlOrParams))
        {
            $url = Application_Request::getInstance()->buildURL($urlOrParams);
        }
        else
        {
            $url = (string)$urlOrParams;
        }

        $this->mode = self::MODE_LINKED;
        $this->url = $url;
        
        if($newWindow) {
            $this->urlTarget = '_blank';
        } 

        return $this;
    }
    
   /**
    * Whether the button's action is to open a URL.
    * @return boolean
    */
    public function isLinked() : bool
    {
        return $this->mode === self::MODE_LINKED;
    }
    
   /**
    * The URL the button links to (if any).
    * @return string
    */
    public function getURL() : string
    {
        return $this->url;
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function click(string $statement) : self
    {
        return $this->makeClickable($statement);
    }

    /**
     * Turns the button into a javascript click button, which will
     * execute the specified javascript code when clicked.
     *
     * @param string $javascript
     * @return $this
     */
    public function makeClickable(string $javascript) : self
    {
        $this->mode = self::MODE_CLICKABLE;
        $this->javascript = $javascript;

        return $this;
    }

    /**
     * @return string
     */
    public function getJavascript() : string
    {
        return $this->javascript;
    }
    
   /**
    * Retrieves the name of the form being submitted by
    * this button (if any).
    * 
    * @return string
    */
    public function getFormName() : string
    {
        return $this->formName;
    }
    
   /**
    * Whether the button's action is a javascript statement.
    * @return boolean
    */
    public function isClickable() : bool
    {
        return $this->mode == self::MODE_CLICKABLE;
    }
    
    public function isFormSubmit() : bool
    {
        return !empty($this->formName);
    }

    /**
     * Makes the button submit the specified form or datagrid on click.
     *
     * @param string|UI_Form|UI_DataGrid|Application_Interfaces_Formable $subject A form name, or supported form instance.
     * @param boolean $simulate Whether to submit in simulation mode.
     * @return $this
     * @throws Application_Exception
     */
    public function makeClickableSubmit($subject, bool $simulate=false)
    {
        $formName = UI_Form::resolveFormName($subject);
        
        if(!empty($formName))
        {
            $this->formName = $formName;
            
            return $this->makeClickable(UI_Form::renderJSSubmitHandler($subject, $simulate));
        }
        
        throw new Application_Exception(
            'Cannot determine form name',
            sprintf(
                'Cannot get form name from subject type [%s].',
                parseVariable($subject)->enableType()->toString()
            ),
            self::ERROR_CANNOT_DETERMINE_FORM_NAME
        );
    }

    public function setOnClick(string $statement) : self
    {
        $this->onclick = $statement;

        return $this;
    }

    /**
     * Makes the button a submit button.
     *
     * @return $this
     */
    public function makeSubmit() : self
    {
        $this->mode = self::MODE_SUBMIT;
        return $this;
    }

    public function isSubmittable() : bool
    {
        return $this->mode === self::MODE_SUBMIT;
    }

    /**
     * Disables the button, so it gets displayed, but not clickable
     *
     * @param string|number|UI_Renderable_Interface $reason If specified, adds a tooltip that explains why the button is disabled.
     * @return $this
     * @throws UI_Exception
     *
     * @see enable()
     */
    public function disable($reason ='') : self
    {
        $this->state = self::STATE_DISABLED;
        
        // to allow using disable() keeping a previously set tooltip
        if(!empty($reason))
        {
            $this->disabledTooltip = toString($reason);
        }

        return $this;
    }

    /**
     * Restore the button's function after a "disable()" call.
     *
     * @see disable()
     * @return $this
     */
    public function enable() : self
    {
        $this->state = self::STATE_ENABLED;

        return $this;
    }
    
   /**
    * Whether the button is disabled.
    * @return boolean
    */
    public function isDisabled() : bool
    {
        return $this->state === self::STATE_DISABLED;
    }
    
    public function isDangerous() : bool
    {
        return $this->layout === ButtonLayoutInterface::LAYOUT_DANGER;
    }

    /**
     * Sets the button style to use. This depends on what the
     * template does with it, default is "normal".
     *
     * @param string $style
     * @return $this
     */
    public function setStyle(string $style) : self
    {
        $this->style = $style;

        return $this;
    }
    
    /**
     * Renders the button using the <code>sidebar.button</code> template.
     * @return string
     * @see template_default_sidebar_button
     */
    protected function _render() : string
    {
        if(!$this->isValid()) 
        {
            return '';
        }
        
        $tpl = $this->createTemplate('sidebar.button');
        $tpl->setVars(array(
            'confirmMessage' => $this->confirmMessage,
            'button' => $this,
            'icon' => $this->icon,
            'name' => $this->name,
            'mode' => $this->mode,
            'url' => $this->url,
            'urlTarget' => $this->urlTarget,
            'state' => $this->state,
            'javascript' => $this->javascript,
            'style' => $this->style,
            'onclick' => $this->onclick,
            'design' => $this->resolveLayout(),
            'loadingText' => $this->loadingText,
            'locked' => $this->isLocked()
        ));

        return $tpl->render();
    }

    /**
     * Sets the tooltip text for the button, which will be
     * shown in the UI as help for the button's function.
     *
     * @param number|string|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($tooltip) : self
    {
        $this->tooltip = toString($tooltip);

        return $this;
    }
    
   /**
    * Sets the value of the button's id attribute, overwrites the default ID.
    * 
    * @param string $id
    * @return $this
    */
    public function setID(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @return $this
     */
    public function setLabel($label) : self
    {
        $this->title = toString($label);
        return $this;
    }

    /**
     * @param number|string|UI_Renderable_Interface $text
     * @return $this
     * @throws UI_Exception
     */
    public function setLoadingText($text) : self
    {
        $this->loadingText = toString($text);
        return $this;
    }

    /**
     * Does the button have a tooltip text?
     * @return boolean
     */
    public function hasTooltip() : bool
    {
        if($this->isDisabled())
        {
            return !empty($this->disabledTooltip);
        }
        
        return !empty($this->tooltip);
    }
    
    public function getTooltip() : string
    {
        if($this->isDisabled())
        {
            return $this->disabledTooltip;
        }
        
        return $this->tooltip;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getLabel() : string
    {
        return $this->title;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function makeActive(bool $active = true): self
    {
        return $this;
    }

    /**
     * @return false
     */
    public function isActive(): bool
    {
        return false;
    }

    protected function resolveLayout(): string
    {
        return $this->layout;
    }
}
