<?php

class UI_Page_Sidebar_Item_Message extends UI_Page_Sidebar_LockableItem
{
   /**
    * @var UI_Message
    */
    protected $message;
    
   /**
    * @param UI_Page_Sidebar $sidebar
    * @param string|number|UI_Renderable_Interface $message
    * @param string $type
    * @param boolean $icon
    * @param boolean $dismissable
    */
    public function __construct(UI_Page_Sidebar $sidebar, $message, $type = UI::MESSAGE_TYPE_INFO, $icon=false, $dismissable=false)
    {
        parent::__construct($sidebar);
        
        $this->message = $this->sidebar->getPage()->createMessage(
            $message, 
            $type, 
            array(
                'dismissable' => $dismissable,
                'icon' => $icon
            )
        );
    }
    
   /**
    * Sets the message, replacing the existing message if any.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @return UI_Page_Sidebar_Item_Message
    */
    public function setMessage($message) : UI_Page_Sidebar_Item_Message
    {
        $this->message->setMessage($message);
        return $this;
    }
    
    public function isValid() : bool
    {
        if($this->isLocked()) 
        {
            return false;
        }
        
        return parent::isValid();
    }
    
    public function makeSlimLayout()
    {
        $this->message->makeSlimLayout();
        return $this;
    }
    
    public function makeLargeLayout()
    {
        $this->message->makeLargeLayout();
        return $this;
    }
    
    public function makeDefaultLayout()
    {
        $this->message->makeDefaultLayout();
        return $this;
    }

    public function isSlimLayout()
    {
        return $this->message->isSlimLayout();
    }
    
    public function makeDismissable()
    {
        $this->message->makeDismissable();
        return $this;
    }
    
    public function makeNotDismissable()
    {
        $this->message->makeNotDismissable();
        return $this;
    }
    
    public function enableIcon(bool $icon=true)
    {
        if($icon)
        {
            $this->message->enableIcon();
        }
        else
        {
            $this->message->disableIcon();
        }
        
        return $this;
    }
    
    public function setCustomIcon(UI_Icon $icon)
    {
        $this->message->setCustomIcon($icon);
        return $this;
    }

    public function disableIcon()
    {
        $this->message->disableIcon();
        return $this;
    }
    
    protected function _render() : string
    {
        $prev = $this->getPreviousSibling();
        if($prev && !$prev->isSeparator()) {
            $this->message->addClass('with-margin-top');
        }
        
        return $this->message->render();
    }

    public function makeInfo()
    {
        $this->message->makeInfo();
        return $this;
    }
    
    public function makeWarning()
    {
        $this->message->makeWarning();
        return $this;
    }
    
    public function makeError()
    {
        $this->message->makeError();
        return $this;
    }
    
    public function makeSuccess()
    {
        $this->message->makeSuccess();
        return $this;
    }
}
