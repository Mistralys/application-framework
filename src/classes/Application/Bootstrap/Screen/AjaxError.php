<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\ImageHelper;

class Application_Bootstrap_Screen_AjaxError extends Application_Bootstrap_Screen
{
    public const DISPATCHER = 'ajax/error.php';

    public function getDispatcher() : string
    {
        return self::DISPATCHER;
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
        
        $url = (string)$request->registerParam('url')->setURL()->get();
        $method = (string)$request->registerParam('method')->setAlnum()->get();
        $message = (string)$request->registerParam('message')->addStringFilter()->addHTMLSpecialcharsFilter()->get();
        $details = (string)$request->registerParam('details')->addStringFilter()->addHTMLSpecialcharsFilter()->get();
        $code = (int)$request->registerParam('code')->setInteger()->get();
        $payload = array();
        $data = array();

        $reqPayload = $request->getJSON('payload');
        if(is_array($reqPayload)) {
            $payload = $reqPayload;
        }

        $reqData = $request->getJSON('data');
        if(is_array($reqData)) {
            $data = $reqData;
        }

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
