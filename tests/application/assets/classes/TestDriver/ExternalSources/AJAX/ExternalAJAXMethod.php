<?php

declare(strict_types=1);

namespace TestDriver\ExternalSources\Ajax;

use Application_AjaxMethod;

class ExternalAJAXMethod extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'ExternalLoadedAJAX';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }
}
