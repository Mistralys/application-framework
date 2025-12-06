<?php

declare(strict_types=1);

namespace Application\API\Admin\RequestTypes;

trait APIClientRequestTrait
{
    private ?APIClientRequestType $apiClientRequestType = null;

    public function getAPIClientRequest(): APIClientRequestType
    {
        if(!isset($this->apiClientRequestType)) {
            $this->apiClientRequestType = new APIClientRequestType($this);
        }

        return $this->apiClientRequestType;
    }
}
