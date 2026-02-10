<?php

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application_AjaxMethod;

class AjaxGetTestJSON extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'GetTestJSON';

    /**
     * @var array<string, string>
     */
    public const array RESPONSE_PAYLOAD = array('success' => 'yes');

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON() : void
    {
        $this->sendJSONResponse(self::RESPONSE_PAYLOAD);
    }
}
