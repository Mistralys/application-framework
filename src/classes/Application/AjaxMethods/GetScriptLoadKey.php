<?php

class Application_AjaxMethods_GetScriptLoadKey extends Application_AjaxMethod
{
    const REGEX_SCRIPT_NAME = '%^([a-z0-9_\-\s\./]+)\.(js|css)$%iU';
    
    public function processJSON()
    {
        $this->request->registerParam('script')->setRegex(self::REGEX_SCRIPT_NAME);
        $script = $this->request->getParam('script');
        if(empty($script)) {
            $this->sendError(t('Invalid or empty script specified.'));
        }
        
        $resource = UI::getInstance()->addResource($script);
        
        $this->sendResponse($resource->toArray());
    }
}
