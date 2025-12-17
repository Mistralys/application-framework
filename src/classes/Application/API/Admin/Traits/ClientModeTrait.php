<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\APIClientsArea;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use UI\AdminURLs\AdminURLInterface;

trait ClientModeTrait
{
    public function createCollection(): APIClientsCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getParentScreenClass() : string
    {
        return APIClientsArea::class;
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }
}
