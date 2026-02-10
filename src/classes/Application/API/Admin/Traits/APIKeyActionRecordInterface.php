<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\MissingRecordInterface;
use UI\AdminURLs\AdminURLInterface;

interface APIKeyActionRecordInterface extends APIKeyActionInterface
{
}
