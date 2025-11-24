<?php
/**
 * @package API
 * @subpackage Clients
 */

declare(strict_types=1);

namespace Application\API\Clients\API\Params;

use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterTrait;
use Application\API\Parameters\Flavors\RequiredOnlyParamInterface;
use Application\API\Parameters\Flavors\RequiredOnlyParamTrait;
use Application\API\Parameters\Type\StringParameter;
use Application\AppFactory;
use AppUtils\RequestHelper;
use Connectors\Headers\HTTPHeadersBasket;

/**
 * API parameter used to specify the API Key for authentication.
 *
 * @package API
 * @subpackage Clients
 */
class APIKeyParam extends StringParameter
    implements
    APIHeaderParameterInterface,
    RequiredOnlyParamInterface
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

    public function getHeaderExample(): string
    {
        return 'Authorization: Bearer '.sb()->tooltip(sb()->bold(sb()->warning('API_KEY')), 'The API Key assigned to your account.');
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

    public function injectHeaderForValue(HTTPHeadersBasket $headers, string $value): self
    {
        $headers->addHeader('Authorization', 'Bearer '.$value);
        return $this;
    }
}
