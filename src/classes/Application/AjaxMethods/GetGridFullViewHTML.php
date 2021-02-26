<?php

class Application_AjaxMethods_GetGridFullViewHTML extends Application_AjaxMethod
{
    public function processJSON()
    {
        $ui = UI::createInstance($this->driver->getApplication());
        $ui->getResourceManager()->clearLoadkeys(); // force all client resources to load in the template
        
        echo $ui->getPage()->renderTemplate(
            'ui/datagrid/fullview'    
        );
    }
}