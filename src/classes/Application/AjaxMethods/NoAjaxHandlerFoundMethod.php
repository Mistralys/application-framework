<?php

declare(strict_types=1);

namespace Application\AjaxMethods;

use Application_AjaxMethod;

class NoAjaxHandlerFoundMethod extends Application_AjaxMethod
{
    public const METHOD_NAME = 'NoAJAXHandlerFound';
    public const ERROR_NO_SUCH_METHOD = 14501;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

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