<?php

declare(strict_types=1);

namespace Application\Campaigns\API\ParamRules;

use Application\API\Parameters\ParamSetInterface;
use Application\Campaigns\CampaignRecord;

interface CampaignSetInterface extends ParamSetInterface
{
    public function getCampaign() : ?CampaignRecord;
}
