<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\APIClientsArea;
use Application\API\Admin\Screens\Mode\View\APIKeysSubmode;
use Application\API\Admin\Screens\Mode\View\ClientSettingsSubmode;
use Application\API\Admin\Screens\Mode\ViewClientMode;
use Application\API\Clients\APIClientRecord;
use DBHelper\Admin\Traits\RecordStatusScreenInterface;
use TestDriver\ClassFactory;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class APIClientRecordURLs
{
    private APIClientRecord $record;

    public function __construct(APIClientRecord $record)
    {
        $this->record = $record;
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(RecordStatusScreenInterface::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(APIClientsArea::URL_NAME)
            ->mode(ViewClientMode::URL_NAME)
            ->int(ClassFactory::createAPIClients()->getRecordRequestPrimaryName(), $this->record->getID());
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ClientSettingsSubmode::URL_NAME);
    }

    public function apiKeys() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(APIKeysSubmode::URL_NAME);
    }
}
