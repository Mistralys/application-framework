<?php

declare(strict_types=1);

namespace Application\Campaigns\API\ParamRules;

use Application\API\Parameters\ParamSet;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\API\Params\CampaignIDParam;
use Application\Campaigns\CampaignRecord;

class CampaignIDSet extends ParamSet implements CampaignSetInterface
{
    public const string SET_ID = 'CampaignID';
    private CampaignIDParam $param;

    public function __construct(CampaignAPIInterface $method)
    {
        $this->param = $method->manageCampaignParams()->manageID()->register();

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
