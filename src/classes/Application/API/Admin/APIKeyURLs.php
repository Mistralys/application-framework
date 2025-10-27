<?php

declare(strict_types=1);

namespace Application\API\Admin;

use Application\API\Admin\Screens\APIKeys\BaseAPIKeyStatusAction;
use Application\API\Clients\Keys\APIKeyRecord;
use UI\AdminURLs\AdminURLInterface;

class APIKeyURLs
{
    private APIKeyRecord $record;

    public function __construct(APIKeyRecord $record)
    {
        $this->record = $record;
    }

    public function status() : AdminURLInterface
    {
        return $this->base()
            ->action(BaseAPIKeyStatusAction::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return $this->record
            ->getClient()
            ->adminURL()
            ->apiKeys()
            ->int($this->record->getCollection()->getRecordRequestPrimaryName(), $this->record->getID());
    }
}
