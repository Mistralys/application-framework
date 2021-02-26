<?php

class Application_AjaxMethods_KeepAlive extends Application_AjaxMethod
{
    public function processJSON()
    {
        return $this->sendResponse(array(
            'state' => 'OK'
        ));
    }
}
