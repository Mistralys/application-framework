<?php

declare(strict_types=1);

namespace Application\Campaigns;

use Application_Users_User;
use AppUtils\Microtime;
use DBHelper_BaseRecord;
use DBHelper_DataTable;

class CampaignRecord extends DBHelper_BaseRecord
{
    private ?DBHelper_DataTable $dataManager = null;

    public function getDataManager() : DBHelper_DataTable
    {
        if(!isset($this->dataManager)) {
            $this->dataManager = new CampaignDataTable($this);
        }

        return $this->dataManager;
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)  :void
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(CampaignCollection::COL_LABEL);
    }

    public function getAlias(): string
    {
        return $this->getRecordStringKey(CampaignCollection::COL_ALIAS);
    }

    public function getDateCreated() : Microtime
    {
        return $this->requireRecordMicrotimeKey(CampaignCollection::COL_CREATED);
    }

    public function getAuthorID() : int
    {
        return $this->getRecordIntKey(CampaignCollection::COL_USER_ID);
    }

    public function getAuthor() : Application_Users_User
    {
        return $this->requireRecordUserKey(CampaignCollection::COL_USER_ID);
    }
}
