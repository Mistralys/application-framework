<?php

declare(strict_types=1);

use AppUtils\ConvertHelper\JSONConverter;

class Application_AjaxMethods_AddJSErrorLog extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'AddJSErrorLog';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON() : void
    {
        $data = JSONConverter::json2array(file_get_contents('php://input'));

        Application_ErrorLog_Log_Entry_JavaScript::logError($data);
    }
}
