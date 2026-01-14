<?php

declare(strict_types=1);

class Application_AjaxMethods_GetScriptLoadKey extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'GetScriptLoadKey';
    public const string REGEX_SCRIPT_NAME = '%^([a-z0-9_\-\s\./]+)\.(js|css)$%iU';
    public const string REQUEST_PARAM_SCRIPT = 'script';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $script = $this->request
            ->registerParam(self::REQUEST_PARAM_SCRIPT)
            ->setRegex(self::REGEX_SCRIPT_NAME)
            ->getString();

        if(empty($script)) {
            $this->sendError(t('Invalid or empty script specified.'));
        }

        $resource = UI::getInstance()->addResource($script);
        
        $this->sendResponse($resource->toArray());
    }
}
