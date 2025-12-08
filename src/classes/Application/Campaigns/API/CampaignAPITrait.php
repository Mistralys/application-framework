<?php

declare(strict_types=1);

namespace Application\Campaigns\API;

/**
 * @see CampaignAPIInterface
 */
trait CampaignAPITrait
{
    private ?CampaignParamsManager $campaignParamsManager = null;

    public function manageCampaignParams() : CampaignParamsManager
    {
        if(!isset($this->campaignParamsManager)) {
            $this->campaignParamsManager = new CampaignParamsManager($this);
        }

        return $this->campaignParamsManager;
    }
}
