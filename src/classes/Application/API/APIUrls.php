<?php

declare(strict_types=1);

namespace Application\API;

use Application\Bootstrap\Screen\APIDocumentationBootstrap;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class APIUrls
{
    public function documentationOverview() : AdminURLInterface
    {
        return AdminURL::create()
            ->dispatcher(APIDocumentationBootstrap::DISPATCHER);
    }

    public function methodDocumentation(APIMethodInterface|string $method) : AdminURLInterface
    {
        if($method instanceof APIMethodInterface) {
            $method = $method->getMethodName();
        }

        return AdminURL::create()
            ->string(APIMethodInterface::REQUEST_PARAM_METHOD, $method)
            ->dispatcher(APIDocumentationBootstrap::DISPATCHER);
    }
}
