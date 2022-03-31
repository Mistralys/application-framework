<?php
/**
 * File containing the {@see UI_ClientConfirmable_Message} class.
 *
 * @see UI_ClientConfirmable_Message
 *@subpackage UserInterface
 * @package Application
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
class UI_ClientConfirmable_Message
{
    public const ERROR_UNSUPPORTED_ELEMENT_MODE = 54401;

    protected string $commentsRequestVar = 'confirm_comments';
    protected UI_Interfaces_ClientConfirmable $uiElement;
    protected string $message = '';
    protected bool $withInput = false;
    protected string $loaderText = '';
    protected UI $ui;
    private bool $withComments = false;
    private string $commentsDesc = '';

    /**
     * @param UI_Interfaces_ClientConfirmable $uiElement
     */
    public function __construct(UI_Interfaces_ClientConfirmable $uiElement)
    {
        $this->uiElement = $uiElement;
        $this->ui = $uiElement->getUI();
    }

    /**
     * Sets the message body of the dialog. May contain HTML.
     *
     * @param string|int|float|UI_Renderable_Interface|NULL $message
     * @return UI_ClientConfirmable_Message
     * @throws UI_Exception
     */
    public function setMessage($message) : UI_ClientConfirmable_Message
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
    public function makeWithInput(bool $withInput=true) : UI_ClientConfirmable_Message
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
     * @see UI_ClientConfirmable_Message::setCommentsDescription()
     * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
     */
    public function makeWithComments(bool $withComments=true) : UI_ClientConfirmable_Message
    {
        $this->withComments = $withComments;

        return $this;
    }

    /**
     * Sets a description text for the comments field (used only if
     * the comment field is enabled).
     *
     * @param string $description
     * @return $this
     */
    public function setCommentsDescription(string $description) : UI_ClientConfirmable_Message
    {
        $this->commentsDesc = $description;

        return $this;
    }


    /**
     * Sets the text to display in the loader shown when the user
     * confirms (to replace the default loading text).
     *
     * @param string|int|float|UI_Renderable_Interface|NULL $text
     * @throws UI_Exception
     */
    public function setLoaderText($text) : UI_ClientConfirmable_Message
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
     * @see UI_ClientConfirmable_Message::getCommentsRequestVar()
     */
    public function setCommentsRequestVar(string $name) : UI_ClientConfirmable_Message
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
     * @see UI_ClientConfirmable_Message::setCommentsRequestVar()
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

        if($this->uiElement->isLinked())
        {
            $code .= sprintf(
                ".MakeLinked(%s, %s)",
                JSHelper::phpVariable2AttributeJS($this->uiElement->getURL()),
                JSHelper::phpVariable2AttributeJS($this->loaderText)
            );
        }
        else if($this->uiElement->isClickable())
        {
            $code .= sprintf(
                ".MakeClickable(function(comments) {%s})",
                $this->uiElement->getJavascript()
            );
        }
        else if($this->uiElement->isSubmittable())
        {
            // STOPPED HERE
            $code .= "";
        }
        else
        {
            throw new Application_Exception(
                'Confirmation dialog not available for UI element configuration.',
                sprintf(
                'It is only available for linked or clickable elements: either the isClickable() or isLinked() methods must return true in the [%s] class.',
                    get_class($this->uiElement)
                ),
                self::ERROR_UNSUPPORTED_ELEMENT_MODE
            );
        }

        if($this->uiElement->isDangerous())
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
