<?php

use AppUtils\ConvertHelper;
use AppUtils\ImageHelper;

class Application_Bootstrap_Screen_AjaxError extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return 'ajax/error.php';
    }

    /**
     * @var string[]
     */
    protected array $excludeVars = array(
        '_loadkeys'
    );
    
    protected function _boot() : void
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

        ConvertHelper::arrayRemoveKeys($payload, $this->excludeVars);
        
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
        ImageHelper::displayImage($theme->getImagePath('ajax-error.png'));
    }
}
