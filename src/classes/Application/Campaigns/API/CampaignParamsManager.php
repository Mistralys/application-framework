<?php

declare(strict_types=1);

namespace Application\Campaigns\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer;
use Application\Campaigns\API\Params\CampaignIDHandler;
use Application\Campaigns\API\Params\CampaignNameHandler;
use Application\Campaigns\CampaignRecord;
use AppUtils\ClassHelper;

/**
 * @method CampaignAPIInterface getMethod()
 */
class CampaignParamsManager extends BaseParamsHandlerContainer
{
    public function __construct(CampaignAPIInterface $method)
    {
        parent::__construct($method);
    }

    private ?CampaignIDHandler $campaignIDHandler = null;

    public function manageID() : CampaignIDHandler
    {
        if(!isset($this->campaignIDHandler)) {
            $this->campaignIDHandler = new CampaignIDHandler($this->getMethod());
            $this->registerHandler($this->campaignIDHandler);
        }

        return $this->campaignIDHandler;
    }

    private ?CampaignNameHandler $campaignNameHandler = null;

    public function manageName() : CampaignNameHandler
    {
        if(!isset($this->campaignNameHandler)) {
            $this->campaignNameHandler = new CampaignNameHandler($this->getMethod());
            $this->registerHandler($this->campaignNameHandler);
        }

        return $this->campaignNameHandler;
    }

    public function getCampaign() : ?CampaignRecord
    {
        return $this->resolveValue();
    }

    public function requireCampaign() : CampaignRecord
    {
        return $this->requireValue();
    }

    public function resolveValue(): ?CampaignRecord
    {
        $value = parent::resolveValue();

        if($value instanceof CampaignRecord) {
            return $value;
        }

        return null;
    }

    public function requireValue(): CampaignRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            CampaignRecord::class,
            parent::requireValue()
        );
    }

    protected function isValidValueType(float|object|array|bool|int|string $value): bool
    {
        return $value instanceof CampaignRecord;
    }
}
