<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode;

use Application\Interfaces\Admin\CollectionSettingsExtendedInterface;
use Application_Admin_Area_Mode_Submode;
use Application_Traits_Admin_CollectionSettings;

abstract class BaseCollectionEditExtended extends Application_Admin_Area_Mode_Submode implements CollectionSettingsExtendedInterface
{
    use Application_Traits_Admin_CollectionSettings;

    public function isEditMode() : bool
    {
        return true;
    }

    public function getDefaultAction() : string
    {
        return '';
    }
}
