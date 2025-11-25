<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use UI\AdminURLs\AdminURLInterface;

/**
 * @method APIClientRecord getRecord()
 * @method APIClientsCollection getCollection()
 */
trait APIClientRecordScreenTrait
{
    public function createCollection(): APIClientsCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }
}
