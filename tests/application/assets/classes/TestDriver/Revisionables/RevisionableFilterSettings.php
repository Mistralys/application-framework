<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application_RevisionableCollection_FilterSettings;

class RevisionableFilterSettings extends Application_RevisionableCollection_FilterSettings
{
    protected function registerSettings(): void
    {
        $this->registerStateSetting();
    }

    protected function _configureFilters(): void
    {
    }
}
