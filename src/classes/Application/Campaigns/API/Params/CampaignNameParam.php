<?php

declare(strict_types=1);

namespace Application\Campaigns\API\Params;

use Application\API\Parameters\Type\StringParameter;
use Application\AppFactory;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\CampaignRecord;

class CampaignNameParam extends StringParameter implements CampaignParamInterface
{
    public function __construct()
    {
        parent::__construct(
            CampaignAPIInterface::PARAM_CAMPAIGN_NAME,
            'Campaign Name'
        );
    }

    public function getCampaignName() : ?string
    {
        return $this->getValue();
    }

    public function getCampaign() : ?CampaignRecord
    {
        $name = $this->getCampaignName();
        if ($name === null) {
            return null;
        }

        $collection = AppFactory::createCampaigns();
        if($collection->aliasExists($name)) {
            return $collection->getByAlias($name);
        }

        return null;
    }
}
