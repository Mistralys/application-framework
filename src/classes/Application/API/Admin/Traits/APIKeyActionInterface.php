<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\MissingRecordInterface;

interface APIKeyActionInterface extends
    APIClientRequestInterface,
    AdminActionInterface,
    MissingRecordInterface,
    ClassLoaderScreenInterface
{
}
