<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use UI\AdminURLs\AdminURLInterface;

interface APIKeyActionInterface extends APIClientRequestInterface
{
    public function getCollection() : APIKeysCollection;
    public function getRecord() : APIKeyRecord;
    public function getRecordMissingURL(): string|AdminURLInterface;
}
