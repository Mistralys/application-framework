<?php

declare(strict_types=1);

namespace Application\Users;

use DBHelper_BaseFilterSettings;

class UsersFilterSettings extends DBHelper_BaseFilterSettings
{
    protected function registerSettings() : void
    {
        $this->registerSearchSetting();
    }

    protected function _configureFilters() : void
    {
    }
}

