<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\APIKeys\BaseAPIKeysListAction;
use Application\API\Admin\Screens\APIKeys\BaseCreateAPIKeyAction;
use Application\API\Clients\Keys\APIKeysCollection;
use UI\AdminURLs\AdminURLInterface;

class APIKeyCollectionURLs
{
    private APIKeysCollection $collection;

    public function __construct(APIKeysCollection $collection)
    {
        $this->collection = $collection;
    }

    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->action(BaseAPIKeysListAction::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return $this->collection->getParentRecord()->adminURL()->apiKeys();
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->action(BaseCreateAPIKeyAction::URL_NAME);
    }
}
