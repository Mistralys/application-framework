<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\BaseAPIClientsArea;
use Application\API\Admin\Screens\BaseClientsListMode;
use Application\API\Admin\Screens\BaseCreateAPIClientMode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class APICollectionURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(BaseClientsListMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseAPIClientsArea::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(BaseCreateAPIClientMode::URL_NAME);
    }
}
