<?php

declare(strict_types=1);

namespace Application\Campaigns\API\ParamRules;

use Application\API\Parameters\ParamSet;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\API\Params\CampaignNameParam;
use Application\Campaigns\CampaignRecord;

class CampaignNameSet extends ParamSet implements CampaignSetInterface
{
    public const string SET_ID = 'CampaignName';
    private CampaignNameParam $param;

    public function __construct(CampaignAPIInterface $method)
    {
        $this->param = $method->manageCampaignParams()->manageName()->register();

        parent::__construct(
            self::SET_ID,
            array(
                $this->param,
            )
        );
    }

    public function getCampaign() : ?CampaignRecord
    {
        return $this->param->getCampaign();
    }
}
