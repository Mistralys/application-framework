<?php

declare(strict_types=1);

namespace Application\API\Parameters\Reserved;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\API\Parameters\Type\StringParameter;

class APIMethodParameter extends StringParameter implements ReservedParamInterface
{
    public function __construct()
    {
        parent::__construct(APIMethodInterface::REQUEST_PARAM_METHOD, 'Method');

        $this
            ->validateByValueExistsCallback(function(mixed $value) : bool{
                return is_string($value) && APIManager::getInstance()->getMethodIndex()->methodExists($value);
            })
            ->setDescription('The name of the API method to call.')
            ->makeRequired();
    }
}
