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
    * @return UI_Page_Sidebar_Item_Button_ConfirmMessage
    */
    public function makeWithInput(bool $withInput=true) : UI_Page_Sidebar_Item_Button_ConfirmMessage
    {
        $this->withInput = $withInput;
        
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
    
    public function getJavaScript() : string
    {
        $jsID = nextJSID();
        
        $this->ui->addJavascriptHeadVariable($jsID, $this->message);
        
        $js = '';
        
        if($this->button->isLinked())
        {
            $js = sprintf(
                "application.redirect('%s', '%s')",
                $this->button->getURL(),
                $this->loaderText
            );
        }
        else if($this->button->isClickable())
        {
            $js = $this->button->getJavascript();
        }
        else 
        {
            throw new Application_Exception(
                'Confirmation dialog not available for sidebar button configuration.',
                'It is only available for linked or clickable buttons.',
                self::ERROR_UNSUPPORTED_BUTTON_MODE
            );
        }
        
        $code = sprintf(
            "application.createConfirmationDialog(%s, function() { %s })",
            $jsID,
            $js
        );
        
        if($this->button->isDangerous())
        {
            $code .= ".MakeDangerous()";
        }
        
        if($this->withInput) 
        {
            $code .= '.MakeWithInput()';
        }
        
        $code .= ".Show();";
        
        return $code;
    }
}
