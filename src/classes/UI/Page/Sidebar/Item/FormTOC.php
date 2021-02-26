<?php

class UI_Page_Sidebar_Item_FormTOC extends UI_Page_Sidebar_Item
{
   /**
    * @var UI_Form
    */
    protected $form;
    
    public function __construct(UI_Page_Sidebar $sidebar, UI_Form $form)
    {
        parent::__construct($sidebar);
        
        $this->form = $form; 
    }
    
    protected function _render()
    {
        // tell the form to add clientside information on elements
        // which can be accessed to create the TOC
        $this->form->enableClientRegistry();
        
        $jsID = nextJSID();
        
        $this->ui->addJavascript('sidebar/form-toc.js');
        $this->ui->addStylesheet('ui-form-toc.css');
        
        $this->ui->addJavascriptOnload(sprintf(
            "Sidebar.CreateFormTOC('%s', '%s').Start()",
            $this->form->getName(),
            $jsID
        ));
        
        return '<div id="'.$jsID.'"></div>';
    }
}
