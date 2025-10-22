<?php

declare(strict_types=1);

namespace Application\API\Admin\RequestTypes;

use Application\Interfaces\Admin\AdminScreenInterface;

interface APIClientRequestInterface extends AdminScreenInterface
{
    public function getAPIClientRequest() : APIClientRequestType;
}
