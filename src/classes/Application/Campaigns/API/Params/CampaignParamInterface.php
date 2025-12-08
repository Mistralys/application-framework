<?php

declare(strict_types=1);

namespace Application\Campaigns\API\Params;

use Application\API\Parameters\APIParameterInterface;
use Application\Campaigns\CampaignRecord;

interface CampaignParamInterface extends APIParameterInterface
{
    public function getCampaign() : ?CampaignRecord;
}
