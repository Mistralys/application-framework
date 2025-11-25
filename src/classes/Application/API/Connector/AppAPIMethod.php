<?php

declare(strict_types=1);

namespace Application\API\Connector;

use Application\API\APIMethodInterface;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use Connectors\Headers\HTTPHeadersBasket;
use Connectors_Connector_Method_Post;
use Connectors_Exception;
use Throwable;

class AppAPIMethod extends Connectors_Connector_Method_Post
{
    /**
     * @param string $methodName
     * @param ArrayDataCollection $params
     * @param HTTPHeadersBasket|NULL $headers
     * @return ArrayDataCollection
     * @throws Connectors_Exception
     */
    public function fetchJSON(string $methodName, ArrayDataCollection $params, ?HTTPHeadersBasket $headers=null) : ArrayDataCollection
    {
        $params->setKey(APIMethodInterface::REQUEST_PARAM_METHOD, $methodName);

        try
        {
            $result = $this->createMethodRequest($methodName)
                ->setPOSTParams($params)
                ->setHeaders($headers)
                ->getData();

            $response = $result->getResult();
        }
        catch (Throwable $e)
        {
            if($e instanceof Connectors_Exception) {
                $response = $e->getResponse();
            }

            if(!isset($response)) {
                throw $e;
            }
        }

        $json = $response->getBody();

        return ArrayDataCollection::create(JSONConverter::json2array($json));
    }
}
