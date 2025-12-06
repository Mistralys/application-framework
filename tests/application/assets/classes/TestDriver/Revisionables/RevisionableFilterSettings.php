<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application\Revisionable\Collection\BaseRevisionableFilterSettings;

class RevisionableFilterSettings extends BaseRevisionableFilterSettings
{
    protected function registerSettings(): void
    {
        $this->registerStateSetting();
    }

    protected function _configureFilters(): void
    {
    }
}
