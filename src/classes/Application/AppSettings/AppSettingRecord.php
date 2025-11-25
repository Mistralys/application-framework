<?php

declare(strict_types=1);

namespace Application\AppSettings;

use Application\FilterCriteria\Items\GenericStringItem;
use AppUtils\Microtime;

class AppSettingRecord extends GenericStringItem
{
    public function __construct(array $data = array())
    {
        $keyName = $data[AppSettingsFilterCriteria::COL_DATA_KEY];

        parent::__construct($keyName, $keyName, $data);
    }

    public function getDataValue() : ?string
    {
        return $this->getString(AppSettingsFilterCriteria::COL_DATA_VALUE);
    }

    public function getRole() : string
    {
        return $this->getString(AppSettingsFilterCriteria::COL_ROLE);
    }

    public function getExpiryDate() : ?Microtime
    {
        return $this->getMicrotime(AppSettingsFilterCriteria::COL_EXPIRY_DATE);
    }
}
