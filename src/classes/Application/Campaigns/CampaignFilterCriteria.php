<?php

declare(strict_types=1);

namespace Application\Campaigns;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

class CampaignFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public function __construct(CampaignCollection $collection)
    {
        parent::__construct($collection);
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
