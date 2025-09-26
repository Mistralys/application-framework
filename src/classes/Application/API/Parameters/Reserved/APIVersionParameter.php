<?php

declare(strict_types=1);

namespace Application\API\Parameters\Reserved;

use Application\API\APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\API\Parameters\Type\StringParameter;

class APIVersionParameter extends StringParameter implements ReservedParamInterface
{
    public function __construct(APIMethodInterface $method)
    {
        parent::__construct(APIMethodInterface::REQUEST_PARAM_API_VERSION, 'API Version');

        $this
            ->setDescription(
                'The version of the API to use for this request. If not provided, the current version (v%1$s) will be used. Supported versions are: %2$s',
                $method->getCurrentVersion(),
                implode(', ', $method->getVersions())
            )
            ->validateByEnum($method->getVersions());
    }
}
