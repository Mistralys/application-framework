<?php

class UI_Page_Navigation_Item_InternalLink extends UI_Page_Navigation_Item
{
    /**
     * @var UI_Page
     */
    protected $page;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param UI_Page $page
     * @param string $targetPageID
     * @param string $title
     * @param array<string,string> $params
     * @throws Application_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id, UI_Page $page, string $targetPageID, string  $title, array $params = array())
    {
        parent::__construct($nav, $id);

        $params['page'] = $targetPageID;

        $this->page = $page;
        $this->title = $title;
        $this->params = $params;

        if(!$this->isActive()) 
        {
            return;
        }
        
        $screen = Application_Driver::getInstance()->getActiveScreen();
        
        if($screen instanceof Application_Lockable_Interface && $screen->isLocked()) 
        {
            $this->locked = true;
            $this->addContainerClass('locked');
        }
    }

    public function getURL() : string
    {
        return $this->request->buildURL($this->params);
    }

    public function getType() : string
    {
        return 'internallink';
    }

    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attributes['href'] = $this->getURL();
        $attributes['id'] = 'nav-'.str_replace('.', '-', $this->getURLPath());
        $attributes['class'] = implode(' ', $this->classes);

        if($this->locked)
        {
            $this->setIcon(UI::icon()->locked());
        }
        
        $label = $this->getTitle();
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }
        
        return '<a' . compileAttributes($attributes) . '>' . $label . '</a>';
    }
    
    public function getAdminScreen() : ?Application_Admin_ScreenInterface
    {
        return Application_Driver::getInstance()
            ->getScreenByPath($this->getURLPath());
    }
    
    public function getURLPath() : string
    {
        $tokens = array($this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE]);

        if(isset($this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE]))
        {
            $tokens[] = $this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE];

            if(isset($this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE]))
            {
                $tokens[] = $this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE];

                if(isset($this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION]))
                {
                    $tokens[] = $this->params[Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION];
                }
            }
        }
        
        return implode('.', $tokens);
    }

    public function isActive() : bool
    {

        if(parent::isActive())
        {
            return true;
        }

        foreach ($this->params as $name => $value)
        {
            if ((string)$this->request->getParam($name) !== (string)$value)
            {
                return false;
            }
        }

        return true;
    }
}
