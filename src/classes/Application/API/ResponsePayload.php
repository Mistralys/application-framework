<?php

declare(strict_types=1);

namespace Application\API;

use Application\API\Response\ResponseInterface;
use AppUtils\ArrayDataCollection;

class ResponsePayload extends ArrayDataCollection implements ResponseInterface
{
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method, array $data = array())
    {
        $this->method = $method;

        parent::__construct($data);
    }

    public function getMethod() : APIMethodInterface
    {
        return $this->method;
    }
}
