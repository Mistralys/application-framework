<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode\Action;

use Application\Interfaces\Admin\CollectionSettingsExtendedInterface;
use Application_Admin_Area_Mode_Submode_Action;
use Application_Traits_Admin_CollectionSettings;

abstract class BaseCollectionCreateExtended extends Application_Admin_Area_Mode_Submode_Action implements CollectionSettingsExtendedInterface
{
    use Application_Traits_Admin_CollectionSettings;

    public function isUserAllowedEditing() : bool
    {
        return $this->isUserAllowed();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function isEditMode(): bool
    {
        return false;
    }
}