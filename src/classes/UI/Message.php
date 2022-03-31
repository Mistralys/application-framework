<?php

class UI_Message extends UI_Renderable implements UI_Renderable_Interface
{
    public const ERROR_INVALID_LAYOUT = 35901;
    
    const LAYOUT_DEFAULT = 'default';
    const LAYOUT_SLIM = 'slim';
    const LAYOUT_LARGE = 'large';
    
   /**
    * @var array<string,mixed>
    */
    protected $properties;
    
   /**
    * @param UI $ui
    * @param string|number|UI_Renderable_Interface $message
    * @param string $type
    * @param array<string,mixed> $options
    */
    public function __construct(UI $ui, $message, string $type=UI::MESSAGE_TYPE_INFO, array $options=array())
    {
        parent::__construct($ui->getPage());

        $this->properties = array_merge(
            array(
                'dismissable' => true,
                'icon' => false,
                'layout' => self::LAYOUT_DEFAULT,
                'classes' => array(),
                'add-dot' => true
            ),
            $options
        );
        
        $this->setProperty('type', $type);
        
        $this->setMessage($message);
    }

   /**
    * Sets the message text.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @return UI_Message
    */
    public function setMessage($message) : UI_Message
    {
        return $this->setProperty('message', trim(toString($message)));
    }
    
    protected function setProperty($name, $value) : UI_Message
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
   /**
    * Enables the icon, and sets to use the specified icon.
    * 
    * @param UI_Icon $icon
    * @return UI_Message
    * @see UI_Message::enableIcon()
    */
    public function setCustomIcon(UI_Icon $icon)
    {
        return $this->setIcon($icon);
    }
    
    public function setLayout(string $layout) : UI_Message
    {
        $validLayouts = array(
            self::LAYOUT_DEFAULT, 
            self::LAYOUT_SLIM,
            self::LAYOUT_LARGE
        );
        
        if(!in_array($layout, $validLayouts)) 
        {
            throw new Application_Exception(
                'Invalid message layout',
                sprintf(
                    'The layout [%s] is not a valid layout. Available layouts are [%s].',
                    $layout,
                    implode(', ', $validLayouts)
                ),
                self::ERROR_INVALID_LAYOUT
            );
        }
        
        return $this->setProperty('layout', $layout);
    }
    
    public function makeDismissable()
    {
        return $this->setDismissable(true);
    }
    
    public function makeInfo()
    {
        return $this->setType(UI::MESSAGE_TYPE_INFO);
    }

    public function makeWarning()
    {
        return $this->setType(UI::MESSAGE_TYPE_WARNING);
    }
    
    public function makeError()
    {
        return $this->setType(UI::MESSAGE_TYPE_ERROR);
    }
    
    public function makeSuccess()
    {
        return $this->setType(UI::MESSAGE_TYPE_SUCCESS);
    }
    
    public function setType($type)
    {
        return $this->setProperty('type', $type);
    }
    
   /**
    * Makes the whole message box inline, so it can be integrated into text.
    * @return UI_Message
    */
    public function makeInline()
    {
        return $this->addClass('alert-inline');
    }
    
    public function makeSlimLayout()
    {
        return $this->setLayout(self::LAYOUT_SLIM);
    }

    public function makeLargeLayout()
    {
        return $this->setLayout(self::LAYOUT_LARGE);
    }
    
    public function makeDefaultLayout()
    {
        return $this->setLayout(self::LAYOUT_DEFAULT);
    }
    
    /**
     * Enables the icon that is automatically adjusted to
     * the message type, i.e. an information icon for an
     * information message for example.
     *
     * @return UI_Message
     * @see UI_Message::disableIcon()
     */
    public function enableIcon()
    {
        return $this->setIcon(true);
    }
    
    public function makeNotDismissable()
    {
        return $this->setDismissable(false);
    }
    
    public function setDismissable($dismissable=true)
    {
        return $this->setProperty('dismissable', $dismissable);
    }
    
   /**
    * Disables the automatic icon, if it was enabled.
    * @return UI_Message
    * @see UI_Message::enableIcon()
    * @see UI_Message::setCustomIcon()
    */
    public function disableIcon()
    {
        return $this->setIcon(false);
    }
    
    protected function setIcon($icon)
    {
        return $this->setProperty('icon', $icon);
    }
    
    protected function _render()
    {
        $vars = $this->properties;
        
        if($this->getProperty('add-dot') === true) 
        {
            $message = strval($vars['message']);
            
            // add the missing dot if need be
            $lastChar = mb_substr($message, -1);
            switch ($lastChar) {
                case '>':
                case '.':
                    break;
                    
                default:
                    $vars['message'] = $message.'.';
                    break;
            }
        }
        
        $tpl = $this->page->createTemplate('message');
        $tpl->setVars($vars);
        
        return $tpl->render();
    }

    public function isSlimLayout()
    {
        return $this->isLayout(self::LAYOUT_SLIM);
    }
    
    public function isDefaultLayout()
    {
        return $this->isLayout(self::LAYOUT_DEFAULT);
    }
    
    public function isLayout($layout)
    {
        return $this->getProperty('layout') == $layout;
    }
    
    public function addClass($className)
    {
        $classes = $this->getProperty('classes');
        if(!in_array($className, $classes)) {
            $classes[] = $className;
        }
        
        return $this->setProperty('classes', $classes);
    }
    
    protected function getProperty($name, $default=null)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        return $default;
    }
}