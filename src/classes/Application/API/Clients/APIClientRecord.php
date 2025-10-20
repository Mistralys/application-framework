<?php

declare(strict_types=1);

namespace Application\API\Clients;

use Application\API\Admin\APIClientRecordURLs;
use Application\API\Admin\APIScreenRights;
use Application\AppFactory;
use Application_Users_User;
use AppUtils\Microtime;
use DBHelper_BaseRecord;

class APIClientRecord extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(APIClientsCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->linkRight(
            $this->getLabel(), $this->adminURL()->status(),
            APIScreenRights::SCREEN_CLIENTS_VIEW_STATUS
        );
    }

    public function getForeignID(): string
    {
        return $this->getRecordStringKey(APIClientsCollection::COL_FOREIGN_ID);
    }

    public function getDateCreated(): Microtime
    {
        return $this->requireRecordMicrotimeKey(APIClientsCollection::COL_DATE_CREATED);
    }

    public function getCreatedByID(): int
    {
        return $this->getRecordIntKey(APIClientsCollection::COL_CREATED_BY);
    }

    public function getCreatedBy() : Application_Users_User
    {
        return AppFactory::createUsers()->getByID($this->getCreatedByID());
    }

    public function isActive(): bool
    {
        return $this->getRecordBooleanKey(APIClientsCollection::COL_IS_ACTIVE);
    }

    public function getComments(): string
    {
        return $this->getRecordStringKey(APIClientsCollection::COL_COMMENTS);
    }

    private ?APIClientRecordURLs $urls = null;

    public function adminURL() : APIClientRecordURLs
    {
        if(!isset($this->urls)) {
            $this->urls = new APIClientRecordURLs($this);
        }

        return $this->urls;
    }
}
