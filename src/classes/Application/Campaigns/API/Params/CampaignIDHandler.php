<?php

declare(strict_types=1);

namespace Application\Campaigns\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\CampaignRecord;

/**
 * @method CampaignAPIInterface getMethod()
 * @method CampaignIDParam register()
 */
class CampaignIDHandler extends BaseParamHandler
{
    public function __construct(CampaignAPIInterface $method)
    {
        parent::__construct($method);
    }

    protected function resolveValueFromSubject(): ?CampaignRecord
    {
        return $this->getParam()?->getCampaign();
    }

    public function getParam(): ?CampaignIDParam
    {
        $param = parent::getParam();

        if($param instanceof CampaignIDParam) {
            return $param;
        }

        return null;
    }

    protected function createParam(): CampaignIDParam
    {
        return new CampaignIDParam();
    }
}
