<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\Mode\View\APIKeysSubmode;
use Application\API\Clients\Keys\APIKeysCollection;
use UI\AdminURLs\AdminURLInterface;

trait APIKeyActionTrait
{
    public function getParentScreenClass() : string
    {
        return APIKeysSubmode::class;
    }

    public function createCollection(): APIKeysCollection
    {
        return $this->getAPIClientRequest()->getRecordOrRedirect()->createAPIKeys();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURLs()->list();
    }
}
