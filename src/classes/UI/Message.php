<?php

use UI\Interfaces\CapturableInterface;
use UI\Interfaces\MessageLayoutInterface;
use UI\Traits\CapturableTrait;

class UI_Message extends UI_Renderable implements MessageLayoutInterface, CapturableInterface
{
    use CapturableTrait;

    public const ERROR_INVALID_LAYOUT = 35901;

    public const LAYOUT_DEFAULT = 'default';
    public const LAYOUT_SLIM = 'slim';
    public const LAYOUT_LARGE = 'large';
    
   /**
    * @var array<string,mixed>
    */
    protected array $properties;

    /**
     * @param UI $ui
     * @param string|number|UI_Renderable_Interface|NULL $message
     * @param string $type
     * @param array<string,mixed> $options
     * @throws Exception
     */
    public function __construct(UI $ui, $message=null, string $type=UI::MESSAGE_TYPE_INFO, array $options=array())
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
     * @param string|number|UI_Renderable_Interface|NULL $message
     * @return $this
     * @throws UI_Exception
     */
    public function setMessage($message) : self
    {
        return $this->setProperty('message', trim(toString($message)));
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected function setProperty(string $name, $value) : self
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
   /**
    * Enables the icon, and sets to use the specified icon.
    * 
    * @param UI_Icon $icon
    * @return $this
    * @see UI_Message::enableIcon()
    */
    public function setCustomIcon(UI_Icon $icon) : self
    {
        return $this->setIcon($icon);
    }

    /**
     * @param string $layout
     * @return $this
     * @throws UI_Exception
     */
    public function setLayout(string $layout) : self
    {
        $validLayouts = array(
            self::LAYOUT_DEFAULT, 
            self::LAYOUT_SLIM,
            self::LAYOUT_LARGE
        );
        
        if(!in_array($layout, $validLayouts)) 
        {
            throw new UI_Exception(
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

    /**
     * @return $this
     */
    public function makeDismissable() : self
    {
        return $this->setDismissable();
    }

    /**
     * @return $this
     */
    public function makeInfo() : self
    {
        return $this->setType(UI::MESSAGE_TYPE_INFO);
    }

    /**
     * @return $this
     */
    public function makeWarning() : self
    {
        return $this->setType(UI::MESSAGE_TYPE_WARNING);
    }

    public function makeWarningXL() : self
    {
        return $this->setType(UI::MESSAGE_TYPE_WARNING_XL);
    }

    /**
     * @return $this
     */
    public function makeError() : self
    {
        return $this->setType(UI::MESSAGE_TYPE_ERROR);
    }

    /**
     * @return $this
     */
    public function makeSuccess() : self
    {
        return $this->setType(UI::MESSAGE_TYPE_SUCCESS);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type) : self
    {
        return $this->setProperty('type', $type);
    }
    
   /**
    * Makes the whole message box inline, so it can be integrated into text.
    * @return UI_Message
    */
    public function makeInline() : self
    {
        return $this->setProperty('inline', true);
    }

    /**
     * @return $this
     * @throws UI_Exception
     */
    public function makeSlimLayout() : self
    {
        return $this->setLayout(self::LAYOUT_SLIM);
    }

    /**
     * @return $this
     * @throws UI_Exception
     */
    public function makeLargeLayout() : self
    {
        return $this->setLayout(self::LAYOUT_LARGE);
    }

    /**
     * @return $this
     * @throws UI_Exception
     */
    public function makeDefaultLayout() : self
    {
        return $this->setLayout(self::LAYOUT_DEFAULT);
    }
    
    /**
     * Enables the icon that is automatically adjusted to
     * the message type, i.e. an information icon for an
     * information message for example.
     *
     * @return $this
     * @see UI_Message::disableIcon()
     */
    public function enableIcon() : self
    {
        return $this->setIcon(true);
    }

    /**
     * @return $this
     */
    public function makeNotDismissable() : self
    {
        return $this->setDismissable(false);
    }

    /**
     * @param bool $dismissable
     * @return $this
     */
    public function setDismissable(bool $dismissable=true) : self
    {
        return $this->setProperty('dismissable', $dismissable);
    }
    
   /**
    * Disables the automatic icon, if it was enabled.
    * @return $this
    * @see UI_Message::enableIcon()
    * @see UI_Message::setCustomIcon()
    */
    public function disableIcon() : self
    {
        return $this->setIcon(false);
    }

    /**
     * @param UI_Icon|bool $icon
     * @return $this
     */
    protected function setIcon($icon) : self
    {
        return $this->setProperty('icon', $icon);
    }
    
    protected function _render() : string
    {
        $this->endCapture();

        $vars = $this->properties;
        
        if($this->getProperty('add-dot') === true) 
        {
            $message = (string)$vars['message'];
            
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

    public function isSlimLayout() : bool
    {
        return $this->isLayout(self::LAYOUT_SLIM);
    }
    
    public function isDefaultLayout() : bool
    {
        return $this->isLayout(self::LAYOUT_DEFAULT);
    }
    
    public function isLayout(string $layout) : bool
    {
        return $this->getProperty('layout') === $layout;
    }
    
    public function addClass(string $className) : self
    {
        $classes = $this->getProperty('classes');
        if(!in_array($className, $classes, true)) {
            $classes[] = $className;
        }
        
        return $this->setProperty('classes', $classes);
    }
    
    protected function getProperty(string $name, $default=null)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        return $default;
    }

    public function getMessage() : string
    {
        return $this->getProperty('message');
    }

    public function setContent($content) : self
    {
        return $this->setMessage($content);
    }

    public function appendContent($content): CapturableInterface
    {
        return $this->setMessage($this->getMessage().toString($content));
    }

    public function getContent(): string
    {
        return $this->getMessage();
    }
}