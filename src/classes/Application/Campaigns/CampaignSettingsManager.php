<?php

declare(strict_types=1);

namespace Application\Campaigns;

use Application_Formable;

class CampaignSettingsManager extends \Application_Formable_RecordSettings_Extended
{
    public function __construct(Application_Formable $formable, CampaignCollection $collection, ?CampaignRecord $record = null)
    {
        parent::__construct($formable, $collection, $record);
        $this->setDefaultsUseStorageNames(true);
    }
}
