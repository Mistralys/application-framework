<?php

require_once 'Application/ErrorLog/Log/Entry/AJAX.php';

class Application_Bootstrap_Screen_AjaxError extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'ajax/error.php';
    }
    
    protected $excludeVars = array(
        '_loadkeys'
    );
    
    protected function _boot()
    {
        $this->disableAuthentication();
        $this->createEnvironment();
        
        $request = $this->driver->getRequest();
        
        $url = $request->registerParam('url')->setURL()->get();
        $method = $request->registerParam('method')->setAlnum()->get();
        $message = $request->registerParam('message')->addStringFilter()->addHTMLSpecialcharsFilter()->get();
        $details = $request->registerParam('details')->addStringFilter()->addHTMLSpecialcharsFilter()->get();
        $code = $request->registerParam('code')->setAlnum()->get();
        $payload = $request->getJSON('payload');
        $data = $request->getJSON('data');

        \AppUtils\ConvertHelper::arrayRemoveKeys($payload, $this->excludeVars);
        
        Application_ErrorLog_Log_Entry_AJAX::logError(
            $method,
            $url,
            $code,
            $message,
            $details,
            $payload,
            $data
        );
        
        $theme = $this->app->getTheme();
        
        // since this gets inserted as an image in the document,
        // send the transparent pixel.
        \AppUtils\ImageHelper::displayImage($theme->getImagePath('ajax-error.png'));
    }
}
