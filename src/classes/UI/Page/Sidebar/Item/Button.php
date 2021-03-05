<?php
/**
 * File containing the {@see UI_Page_Sidebar_Item_Button} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Sidebar_Item_Button
 */

use function AppUtils\parseVariable;

/**
 * A single button in the sidebar.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method UI_Page_Sidebar_Item_Button requireChanging(Application_Revisionable $revisionable)
 * @method UI_Page_Sidebar_Item_Button requireTrue(mixed $condition, string $reason=null)
 * @method UI_Page_Sidebar_Item_Button requireFalse(mixed $condition, string $reason=null)
 * @method UI_Page_Sidebar_Item_Button setIcon($icon)
 */
class UI_Page_Sidebar_Item_Button extends UI_Page_Sidebar_LockableItem implements Application_Interfaces_Iconizable
{
    const ERROR_CANNOT_DETERMINE_FORM_NAME = 55301;
    
    use Application_Traits_Iconizable;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mode = 'none';

    protected $url = null;

    protected $javascript = '';

    protected $state = 'enabled';

    protected $style = 'normal';

    protected $onclick = null;

    protected $design = null;

    protected $id;

    /**
     * @var UI_Page_Sidebar_Item_Button_ConfirmMessage|NULL
     */
    protected $confirmMessage;

    /**
     * @param UI_Page_Sidebar $sidebar
     * @param string $name
     * @param string|UI_Renderable_Interface|int|float $title
     */
    public function __construct(UI_Page_Sidebar $sidebar, string $name, $title = '')
    {
        parent::__construct($sidebar);
        $this->name = $name;
        $this->title = toString($title);
        $this->id = 'button_' . $this->name;

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
     * Makes the button link to the specified URL.
     *
     * @param string|array $urlOrParams
     * @param boolean $newWindow Whether to open the link in a new tab/window
     */
    public function makeLinked($urlOrParams, $newWindow=false)
    {
        $url = $urlOrParams;
        if (is_array($urlOrParams)) {
            $url = Application_Request::getInstance()->buildURL($urlOrParams);
        }

        $this->mode = 'linked';
        $this->url = $url;
        
        if($newWindow) {
            $this->urlTarget = '_blank';
        } 

        return $this;
    }
    
   /**
    * Whether the button's action is to open an URL.
    * @return boolean
    */
    public function isLinked()
    {
        return $this->mode == 'linked';
    }
    
   /**
    * The URL the button links to (if any).
    * @return string|NULL
    */
    public function getURL()
    {
        return $this->url;
    }
    
    /**
     * Turns the button into a javascript click button, which will
     * execute the specified javascript code when clicked.
     *
     * @param string $javascript
     * @return UI_Page_Sidebar_Item_Button
     */
    public function makeClickable($javascript)
    {
        $this->mode = 'clickable';
        $this->javascript = $javascript;

        return $this;
    }
    
    public function getJavascript()
    {
        return $this->javascript;
    }
    
   /**
    * @var string
    */
    protected $formName = '';
    
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
    public function isClickable()
    {
        return $this->mode == 'clickable';
    }
    
    public function isFormSubmit() : bool
    {
        return !empty($this->formName);
    }
    
   /**
    * Makes the button submit the specified form or datagrid on click.
    * 
    * @param string|UI_Form|UI_DataGrid|Application_Formable $subject A form name, or supported form instance.
    * @param boolean $simulate Whether to submit in simulation mode.
    * @return UI_Page_Sidebar_Item_Button
    */
    public function makeClickableSubmit($subject, $simulate=false)
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

    public function makePrimary()
    {
        $this->design = 'primary';

        return $this;
    }

    public function makeDangerous()
    {
        $this->design = 'danger';

        return $this;
    }
    
   /**
    * Adds a confirmation dialog with the specified message
    * before the button action is executed. Automatically
    * styles the confirmation dialog according to the button
    * style, e.g. if it's a danger button the dialog will be
    * a dangerous operation dialog.
    * 
    * @param string|number|UI_Renderable_Interface $message Can contain HTML code.
    * @param boolean $withInput Whether to have the user confirm the operation by typing a confirm string.
    * @return UI_Page_Sidebar_Item_Button
    */
    public function makeConfirm($message, bool $withInput=false) : UI_Page_Sidebar_Item_Button
    {
        $this->getConfirmMessage()
        ->setMessage($message)
        ->makeWithInput($withInput);
        
        return $this;
    }
    
   /**
    * Returns the confirm message instance to be able to configure it further.
    * If none exists yet, it is created.
    * 
    * @return UI_Page_Sidebar_Item_Button_ConfirmMessage
    */
    public function getConfirmMessage() : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        if(isset($this->confirmMessage))
        {
            return $this->confirmMessage;
        }
        
        $this->confirmMessage = new UI_Page_Sidebar_Item_Button_ConfirmMessage($this);
        
        return $this->confirmMessage;
    }
    
    public function makeSuccess()
    {
        $this->design = 'success';

        return $this;
    }

    /**
     * Transforms the button into a button styled
     * for a warning before an action.
     *
     * @return UI_Page_Sidebar_Item_Button
     */
    public function makeWarning()
    {
        $this->design = 'warning';

        return $this;
    }

    /**
     * Transforms the button into a developer button
     * that only developers have access to.
     *
     * @return UI_Page_Sidebar_Item_Button
     */
    public function makeDeveloper()
    {
        $this->design = 'developer';

        return $this;
    }

    public function setOnClick($statement)
    {
        $this->onclick = $statement;

        return $this;
    }


    /**
     * Makes the button a submit button.
     *
     * @return UI_Page_Sidebar_Item_Button
     */
    public function makeSubmit()
    {
        $this->mode = 'submit';

        return $this;
    }
    
   /**
    * @var string
    */
    protected $disabledTooltip = '';

    /**
     * Disables the button so it gets displayed, but not clickable
     *
     * @param string|number|UI_Renderable_Interface $helpText If specified, adds a tooltip that explains why the button is disabled.
     * @see enable()
     * @return UI_Page_Sidebar_Item_Button
     */
    public function disable($helpText='')
    {
        $this->state = self::STATE_DISABLED;
        
        // to allow using disable() keeping a previously set tooltip
        if(!empty($helpText)) 
        {
            $this->disabledTooltip = toString($helpText);
        }

        return $this;
    }

    /**
     * Restore the button's function after a disable call.
     *
     * @see disable()
     * @return UI_Page_Sidebar_Item_Button
     */
    public function enable()
    {
        $this->state = self::STATE_ENABLED;

        return $this;
    }
    
    const STATE_DISABLED = 'disabled';
    
    const STATE_ENABLED = 'enabled';
    
   /**
    * Whether the button is disabled.
    * @return boolean
    */
    public function isDisabled()
    {
        return $this->state == self::STATE_DISABLED;
    }
    
    public function isDangerous() : bool
    {
        return $this->design === 'danger';
    }

    /**
     * Sets the button style to use. This depends on what the
     * template does with it, default is "normal".
     *
     * @param string $style
     * @return UI_Page_Sidebar_Item_Button
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }
    
    protected $urlTarget = null;

    /**
     * Renders the button using the <code>sidebar.button</code> template.
     * @return string
     */
    protected function _render()
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
            'design' => $this->design,
            'loadingText' => $this->loadingText,
            'locked' => $this->isLocked()
        ));

        return $tpl->render();
    }

    /**
     * Stores the tooltip text for the button if any.
     * @var string
     * @see setTooltip()
     */
    protected $tooltip = '';

    /**
     * Sets the tooltip text for the button, which will be
     * shown in the UI as help for the button's function.
     *
     * @since 3.3.5
     * @param string $tooltip
     * @return UI_Page_Sidebar_Item_Button
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;

        return $this;
    }
    
   /**
    * Sets the value of the button's id attribute, overwrites the default ID.
    * 
    * @param string $id
    * @return UI_Page_Sidebar_Item_Button
    */
    public function setID($id)
    {
        $this->id = $id;
        return $this;
    }
    
    protected $loadingText = null;
    
    public function setLoadingText($text)
    {
        $this->loadingText = $text;
        return $this;
    }

    /**
     * Does the button have a tooltip text?
     * @return boolean
     */
    public function hasTooltip()
    {
        if($this->isDisabled()) {
            return !empty($this->disabledTooltip);
        }
        
        return !empty($this->tooltip);
    }
    
    public function getTooltip()
    {
        if($this->isDisabled()) {
            return $this->disabledTooltip;
        }
        
        return $this->tooltip;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getLabel()
    {
        return $this->title;
    }
}
