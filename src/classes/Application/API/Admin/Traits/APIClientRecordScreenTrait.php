<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use DBHelper_BaseCollection;

/**
 * @method APIClientRecord getRecord()
 * @method APIClientsCollection getCollection()
 */
trait APIClientRecordScreenTrait
{
    /**
     * @return APIClientsCollection
     */
    public function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getRecordMissingURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }
}
