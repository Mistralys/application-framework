<?php

use AppUtils\AttributeCollection;
use UI\Page\Navigation\LinkItemBase;

class UI_Page_Navigation_Item_InternalLink extends LinkItemBase
{
    protected UI_Page $page;
    protected bool $locked = false;

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

        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = $targetPageID;

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

        $attribs = AttributeCollection::create($attributes)
            ->href($this->getURL())
            ->id($this->generateID())
            ->addClasses($this->classes)
            ->attr('target', $this->target);

        if($this->locked)
        {
            $this->setIcon(UI::icon()->locked());
        }

        if(isset($this->tooltipInfo))
        {
            $this->tooltipInfo->injectAttributes($attribs);
        }
        
        $label = $this->getTitle();
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }
        
        return '<a' . $attribs . '>' . $label . '</a>';
    }

    /**
     * @var array<string,int>
     */
    private static $ids = array();

    private function generateID() : string
    {
        $id = 'nav-'.str_replace('.', '-', $this->getURLPath());

        if(!isset(self::$ids[$id]))
        {
            self::$ids[$id] = 1;
            return $id;
        }

        self::$ids[$id]++;
        return $id.'-'.self::$ids[$id];
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
