<?php

declare(strict_types=1);

namespace Application\API\Clients\API\Params;

use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterTrait;
use Application\API\Parameters\Flavors\RequiredOnlyParamTrait;
use Application\API\Parameters\Type\StringParameter;
use Application\AppFactory;
use AppUtils\RequestHelper;

class APIKeyParam extends StringParameter implements APIHeaderParameterInterface
{
    use APIHeaderParameterTrait;
    use RequiredOnlyParamTrait;

    public function __construct()
    {
        parent::__construct(
            APIKeyMethodInterface::API_KEY_PARAM_NAME,
            'API Key'
        );

        $this->makeRequired();
        $this->setDescription('The API Key used to authenticate the request.');
    }

    public function getHeaderName(): string
    {
        return 'Authorization: Bearer <API Key>';
    }

    public function getHeaderValue(): ?string
    {
        $token = RequestHelper::getBearerToken();

        if($token === null) {
            return null;
        }

        $key = AppFactory::createAPIClients()->findAPIKey($token);
        if($key !== null) {
            return $token;
        }

        return null;
    }

    public function getKey() : ?APIKeyRecord
    {
        $keyValue = $this->getValue();
        if ($keyValue === null) {
            return null;
        }

        return AppFactory::createAPIClients()->findAPIKey($keyValue);
    }
}
