<?php


class Application_Bootstrap_Screen_Ajax extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'ajax/';
    }
    
    protected function _boot()
    {
        $this->enableScriptMode();
        
        $this->createEnvironment();
        
        $ajax = $this->driver->getAjaxHandler();

        try
        {
            $ajax->addMethodsFromFolder(
                $this->driver->getClassesFolder().'/AjaxMethods', 
                APP_CLASS_NAME.'_AjaxMethods'
            );
            
            $ajax->process();
        }
        catch(Exception $e)
        {
            $ajax->displayException($e);
        }
    }
}