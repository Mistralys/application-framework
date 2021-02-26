<?php

class UI_Page_Sidebar_Item_Template extends UI_Page_Sidebar_LockableItem
{
    /**
     * @var UI_Page_Template
     */
    private $template;

    private $disabled = false;

    public function __construct(UI_Page_Sidebar $sidebar, $templateID, $params = array())
    {
        parent::__construct($sidebar);
        
        $this->template = $sidebar->getPage()->createTemplate($templateID);
        $this->template->setVars($params);
    }

    /**
     * @return UI_Page_Template
     */
    public function getTemplate() : UI_Page_Template
    {
        return $this->template;
    }

    public function setVars($vars)
    {
        return $this->template->setVars($vars);
    }

    public function setVar($name, $value)
    {
        return $this->template->setVar($name, $value);
    }

    protected function _render()
    {
        $this->template->setVar('disabled', $this->disabled);
        $this->template->setVar('locked', $this->locked);

        return $this->template->render();
    }

    public function disable()
    {
        $this->disabled = true;
    }
}
