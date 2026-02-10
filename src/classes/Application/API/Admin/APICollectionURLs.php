<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\APIClientsArea;
use Application\API\Admin\Screens\Mode\ClientsListMode;
use Application\API\Admin\Screens\Mode\CreateClientMode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class APICollectionURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(ClientsListMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(APIClientsArea::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateClientMode::URL_NAME);
    }
}
