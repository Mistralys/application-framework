<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\Mode\ViewClientMode;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use UI\AdminURLs\AdminURLInterface;

trait ClientSubmodeTrait
{
    public function createCollection(): APIClientsCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getParentScreenClass(): ?string
    {
        return ViewClientMode::class;
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }

    public function getRecordMissingURL() : AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }
}