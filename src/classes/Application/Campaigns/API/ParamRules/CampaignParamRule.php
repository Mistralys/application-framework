<?php

declare(strict_types=1);

namespace Application\Campaigns\API\ParamRules;

use Application\API\Parameters\Rules\Type\OrRule;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\CampaignRecord;

class CampaignParamRule extends OrRule
{
    public function __construct(CampaignAPIInterface $method)
    {
        parent::__construct('Selecting the campaign');

        $this
            ->addSet(new CampaignIDSet($method))
            ->addSet(new CampaignNameSet($method));
    }

    public function getCampaign() : ?CampaignRecord
    {
        $validSet = $this->getValidSet();

        if($validSet instanceof CampaignSetInterface) {
            return $validSet->getCampaign();
        }

        return null;
    }
}
