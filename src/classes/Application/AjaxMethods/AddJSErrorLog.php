<?php

class Application_AjaxMethods_AddJSErrorLog extends Application_AjaxMethod
{
    public const METHOD_NAME = 'AddJSErrorLog';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON() : void
    {
        $message = $this->request->getParam('message');
        $details = $this->request->getParam('details');
        $column = $this->request->registerParam('column')->setInteger()->get(0);
        $line = $this->request->registerParam('line')->setInteger()->get(0);
        $referer = $this->request->registerParam('referer')->setURL()->get();
        $url = $this->request->registerParam('url')->setURL()->get();
        $code = $this->request->registerParam('code')->setInteger()->get(0);
        $type = $this->request->registerParam('type')->setAlnum()->get();

        Application_ErrorLog_Log_Entry_JavaScript::logError(
            $code,
            $type,
            $message,
            $details,
            $referer,
            $url,
            $line,
            $column
        );
    }
}
