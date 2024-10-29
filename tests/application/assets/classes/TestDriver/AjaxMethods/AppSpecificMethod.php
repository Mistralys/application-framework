<?php

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application_AjaxMethod;

class AppSpecificMethod extends Application_AjaxMethod
{
    public const METHOD_NAME = 'AppSpecificMethod';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON() : void
    {
        $this->sendJSONResponse(['success' => 'yes']);
    }
}
