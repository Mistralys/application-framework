<?php

declare(strict_types=1);

class UI_Form_Renderer_Tabs
{
   /**
    * @var UI_Form_Renderer
    */
    private $renderer;
    
   /**
    * @var UI
    */
    private $ui;
    
   /**
    * @var UI_Form_Renderer_Tabs_Tab[]
    */
    private $tabs = array();
    
    public function __construct(UI_Form_Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->ui = $renderer->getUI();
    }
    
    public function create(UI_Form_Renderer_ElementFilter_RenderDef $renderDef, UI_Form_Renderer_ElementFilter $elements) : UI_Form_Renderer_Tabs_Tab
    {
        $tab = new UI_Form_Renderer_Tabs_Tab($renderDef, $elements);
        
        $this->tabs[] = $tab;
        
        return $tab;
    }
    
    public function render() : string
    {
        if(empty($this->tabs))
        {
            return '';
        }
        
        $tabs = $this->ui->createTabs($this->renderer->getID().'-tabs');
        
        foreach($this->tabs as $tabDef)
        {
            $tab = $tabs->appendTab($tabDef->renderLabel(), $tabDef->getID());
            
            $tab->setContent(
                $tabDef->renderAbstract().
                $tabDef->renderContent()
            );
        }
        
        $tabs->selectTab($tabs->getTabByName($this->getActiveTabID()));
        
        return $tabs->render();
    }
    
    private function getActiveTabID() : string
    {
        if(empty($this->tabs))
        {
            return '';
        }
        
        foreach($this->tabs as $tab)
        {
            if($tab->hasErrors())
            {
                return $tab->getID();
            }
        }
        
        return $this->tabs[0]->getID();
    }
}
