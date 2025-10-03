<?php

declare(strict_types=1);

namespace Application\API\Connector;

use Application\API\APIMethodInterface;
use AppUtils\ArrayDataCollection;
use Connectors_Connector_Method_Post;

class AppAPIMethod extends Connectors_Connector_Method_Post
{
    public function fetchJSON(string $methodName, ArrayDataCollection $params) : ArrayDataCollection
    {
        $params->setKey(APIMethodInterface::REQUEST_PARAM_METHOD, $methodName);

        $response = $this->createMethodRequest($methodName)
            ->setPOSTParams($params)
            ->getData();

        return ArrayDataCollection::create($response->getData());
    }
}
