<?php

declare(strict_types=1);

class Application_AjaxMethods_NoAJAXHandlerFound extends Application_AjaxMethod
{
    const ERROR_NO_SUCH_METHOD = 14501;
    
    public function processJSON()
    {
        $this->sendError(
            'No such AJAX method',
            array(
                'details' => sprintf(
                'No AJAX method found for the request parameters [%s].',
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)
                )
            ),
            self::ERROR_NO_SUCH_METHOD
        );
    }
}