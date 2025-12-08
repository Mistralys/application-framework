<?php

declare(strict_types=1);

namespace Application\Campaigns\API\ParamRules;

use Application\API\Parameters\Handlers\BaseRuleHandler;
use Application\API\Parameters\Rules\RuleInterface;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\CampaignRecord;

/**
 * @method CampaignAPIInterface getMethod()
 */
class CampaignRuleHandler extends BaseRuleHandler
{
    public function __construct(CampaignAPIInterface $method)
    {
        parent::__construct($method);
    }

    protected function resolveValueFromSubject(): ?CampaignRecord
    {
        return $this->getRule()?->getCampaign();
    }

    public function getRule(): ?CampaignParamRule
    {
        $rule = parent::getRule();

        if($rule instanceof CampaignParamRule) {
            return $rule;
        }

        return null;
    }

    protected function createRule(): CampaignParamRule
    {
        return new CampaignParamRule($this->getMethod());
    }
}
