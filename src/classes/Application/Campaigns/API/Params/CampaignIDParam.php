<?php

declare(strict_types=1);

namespace Application\Campaigns\API\Params;

use Application\API\Parameters\Type\IntegerParameter;
use Application\AppFactory;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\CampaignRecord;

class CampaignIDParam extends IntegerParameter implements CampaignParamInterface
{
    public function __construct()
    {
        parent::__construct(
            CampaignAPIInterface::PARAM_CAMPAIGN_ID,
            'Campaign ID'
        );
    }

    public function getCampaignID() : ?int
    {
        return $this->getValue();
    }

    public function getCampaign() : ?CampaignRecord
    {
        $collection = AppFactory::createCampaigns();
        $campaignID = $this->getCampaignID();

        if($campaignID !== null && $collection->idExists($campaignID)) {
            return $collection->getByID($campaignID);
        }

        return null;
    }
}
