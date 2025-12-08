<?php

declare(strict_types=1);

namespace Application\Campaigns;

use DBHelper_DataTable;

class CampaignDataTable extends DBHelper_DataTable
{
    public const string TABLE_DATA = 'campaign_data';
    public const string PRIMARY_NAME = 'campaign_id';
    public const string COL_KEY_NAME = 'data_key';
    public const string COL_KEY_VALUE = 'data_value';

    public function __construct(CampaignRecord $campaignRecord)
    {
        parent::__construct(
            self::TABLE_DATA,
            self::PRIMARY_NAME,
            $campaignRecord->getID(),
            sprintf('Campaign [%s] | DataTable', $campaignRecord->getID())
        );

        $this->setNameColumnName(self::COL_KEY_NAME);
        $this->setValueColumnName(self::COL_KEY_VALUE);
    }
}
