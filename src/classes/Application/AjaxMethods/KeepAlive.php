<?php

declare(strict_types=1);

/**
 * @see Application_Bootstrap_Screen_Ajax::checkKeepAlive()
 */
class Application_AjaxMethods_KeepAlive extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->sendResponse(array(
            'state' => 'OK'
        ));
    }
}
