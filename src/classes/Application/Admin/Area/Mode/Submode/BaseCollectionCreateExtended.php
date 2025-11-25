<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseCollectionCreateExtended extends Application_Admin_Area_Mode_Submode implements RecordEditScreenInterface
{
    use RecordSettingsScreenTrait;

    public function isUserAllowedEditing() : bool
    {
        return $this->isUserAllowed();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function isEditMode() : bool
    {
        return false;
    }

    public function getDefaultAction() : string
    {
        return '';
    }
}
