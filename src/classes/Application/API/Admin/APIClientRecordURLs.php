<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\APIKeys\BaseAPIKeysSubmode;
use Application\API\Admin\Screens\BaseAPIClientsArea;
use Application\API\Admin\Screens\BaseAPIClientSettingsScreen;
use Application\API\Admin\Screens\BaseAPIClientStatusScreen;
use Application\API\Admin\Screens\BaseViewAPIClientMode;
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
            ->area(BaseAPIClientsArea::URL_NAME)
            ->mode(BaseViewAPIClientMode::URL_NAME)
            ->int(ClassFactory::createAPIClients()->getRecordRequestPrimaryName(), $this->record->getID());
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseAPIClientSettingsScreen::URL_NAME);
    }

    public function apiKeys() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseAPIKeysSubmode::URL_NAME);
    }
}
