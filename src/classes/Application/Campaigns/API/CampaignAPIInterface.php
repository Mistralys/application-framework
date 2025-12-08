<?php

declare(strict_types=1);

namespace Application\Campaigns\API;

use Application\API\APIMethodInterface;

/**
 * @see CampaignAPITrait
 */
interface CampaignAPIInterface extends APIMethodInterface
{
    public const string PARAM_CAMPAIGN_ID = 'campaignID';
    public const string PARAM_CAMPAIGN_NAME = 'campaignName';

    public function manageCampaignParams() : CampaignParamsManager;
}
