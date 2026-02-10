<?php

declare(strict_types=1);

/**
 * @see Application_Bootstrap_Screen_Ajax::checkKeepAlive()
 */
class Application_AjaxMethods_KeepAlive extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'KeepAlive';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $this->sendResponse(array(
            'state' => 'OK'
        ));
    }
}
