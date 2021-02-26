<?php

class UI_Page_Navigation_Item_InternalLink extends UI_Page_Navigation_Item
{
    /**
     * @var UI_Page
     */
    protected $page;

    protected $locked = false;
    
    public function __construct(UI_Page_Navigation $nav, $id, UI_Page $page, $targetPageID, $title, $params = array())
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

    public function getURL()
    {
        return $this->request->buildURL($this->params);
    }

    public function getType()
    {
        return 'internallink';
    }

    public function render($attributes = array())
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attributes = array(
            'href' => $this->getURL(),
            'id' => 'nav-'.str_replace('.', '-', $this->getURLPath()),
            'class' => implode(' ', $this->classes)
        );
        
        if($this->locked) {
            $this->setIcon(UI::icon()->locked());
        }
        
        $label = $this->getTitle();
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }
        
        return '<a' . compileAttributes($attributes) . '>' . $label . '</a>';
    }
    
    public function getAdminScreen()
    {
        $path = $this->getURLPath();
        $driver = Application_Driver::getInstance();
        return $driver->getScreenByPath($path);
    }
    
    public function getURLPath()
    {
        $tokens = array($this->params['page']);
        if(isset($this->params['mode'])) {
            $tokens[] = $this->params['mode'];
            if(isset($this->params['submode'])) {
                $tokens[] = $this->params['submode'];
                if(isset($this->params['action'])) {
                    $tokens[] = $this->params['action'];
                }
            }
        }
        
        return implode('.', $tokens);
    }

    public function isActive()
    {
        $active = $this->nav->getForcedActiveItem();
        if ($active && $active->getID() == $this->id) {
            return true;
        }

        foreach ($this->params as $name => $value) {
            if ($this->request->getParam($name) != $value) {
                return false;
            }
        }

        return true;
    }
}