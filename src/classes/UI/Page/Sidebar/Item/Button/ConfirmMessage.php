<?php
/**
 * File containing the {@see UI_Page_Sidebar_Item_Button_ConfirmMessage} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Sidebar_Item_Button_ConfirmMessage
 */

declare(strict_types=1);

/**
 * Container for a button's confirmation message. Allows
 * customizing the message dialog.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Sidebar_Item_Button_ConfirmMessage
{
    const ERROR_UNSUPPORTED_BUTTON_MODE = 54401;

    /**
     * @var string
     */
    protected $commentsRequestVar = 'confirm_comments';
    
   /**
    * @var UI_Page_Sidebar_Item_Button
    */
    protected $button;
    
   /**
    * @var string
    */
    protected $message = '';
    
   /**
    * @var boolean
    */
    protected $withInput = false;
    
   /**
    * @var string
    */
    protected $loaderText = '';
    
   /**
    * @var UI
    */
    protected $ui;

    /**
     * @var bool
     */
    private $withComments;

    /**
     * @var string
     */
    private $commentsDesc = '';

    /**
    * @param UI_Page_Sidebar_Item_Button $button
    * 
    */
    public function __construct(UI_Page_Sidebar_Item_Button $button)
    {
        $this->button = $button;
        $this->ui = $button->getUI();
    }
    
   /**
    * Sets the message body of the dialog. May contain HTML.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @return UI_Page_Sidebar_Item_Button_ConfirmMessage
    */
    public function setMessage($message) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->message = toString($message);
        
        return $this;
    }
    
   /**
    * Whether to display an input field that the user has to type
    * a confirmation text in to confirm the operation.
    * 
    * @param bool $withInput
    * @return $this
    */
    public function makeWithInput(bool $withInput=true) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->withInput = $withInput;
        
        return $this;
    }

    /**
     * Whether to add a comments input field to enter comments regarding
     * the operation.
     *
     * @param bool $withComments
     * @return $this
     *
     * @see UI_Page_Sidebar_Item_Button_ConfirmMessage::setCommentsDescription()
     * @see UI_Page_Sidebar_Item_Button_ConfirmMessage::getCommentsRequestVar()
     */
    public function makeWithComments(bool $withComments=true) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->withComments = $withComments;

        return $this;
    }

    /**
     * Sets a description text for the comments field (used only if
     * the comments field is enabled).
     *
     * @param string $description
     * @return $this
     */
    public function setCommentsDescription(string $description) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->commentsDesc = $description;

        return $this;
    }

    
   /**
    * Sets the text to display in the loader shown when the user 
    * confirms (to replace the default loading text).
    * 
    * @param string|number|UI_Renderable_Interface $text 
    */
    public function setLoaderText($text) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->loaderText = toString($text);
        
        return $this;
    }

    /**
     * Sets the name of the request variable that is used to add the
     * comments text to the redirect URL when using the `makeWithComments()`
     * method, and a linked button.
     *
     * @param string $name
     * @return $this
     * @see UI_Page_Sidebar_Item_Button_ConfirmMessage::getCommentsRequestVar()
     */
    public function setCommentsRequestVar(string $name) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->commentsRequestVar = $name;
        return $this;
    }

    /**
     * Gets the name of the request variable that is used to add the
     * comments text to the redirect URL when using the `makeWithComments()`
     * method, and a linked button.
     *
     * @return string
     * @see UI_Page_Sidebar_Item_Button_ConfirmMessage::setCommentsRequestVar()
     */
    public function getCommentsRequestVar() : string
    {
        return $this->commentsRequestVar;
    }
    
    public function getJavaScript() : string
    {
        $jsID = nextJSID();
        
        $this->ui->addJavascriptHeadVariable($jsID, $this->message);
        
        $code = sprintf("application.createConfirmationDialog(%s)", $jsID);

        $code .= sprintf(
            ".SetCommentsRequestVar('%s')",
            $this->commentsRequestVar
        );

        if($this->button->isLinked())
        {
            $code .= sprintf(
                ".MakeLinked(%s, %s)",
                JSHelper::phpVariable2AttributeJS($this->button->getURL()),
                JSHelper::phpVariable2AttributeJS($this->loaderText)
            );
        }
        else if($this->button->isClickable())
        {
            $code .= sprintf(
                ".MakeClickable(function(comments) {%s})",
                $this->button->getJavascript()
            );
        }
        else
        {
            throw new Application_Exception(
                'Confirmation dialog not available for sidebar button configuration.',
                'It is only available for linked or clickable buttons.',
                self::ERROR_UNSUPPORTED_BUTTON_MODE
            );
        }

        if($this->button->isDangerous())
        {
            $code .= ".MakeDangerous()";
        }
        
        if($this->withInput) 
        {
            $code .= '.MakeWithInput()';
        }

        if($this->withComments)
        {
            $code .= sprintf(
                '.MakeWithComments(%s)',
                JSHelper::phpVariable2AttributeJS($this->commentsDesc)
            );
        }
        
        $code .= ".Show();";
        
        return $code;
    }
}
