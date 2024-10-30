<?php

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application_AjaxMethod;

class TestJSONFormatMethod extends Application_AjaxMethod
{
    public const METHOD_NAME = 'TestJSONFormat';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON() : void
    {
        $this->sendJSONResponse(array('success' => 'yes'));
    }
}
