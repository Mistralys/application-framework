<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseCollectionEditExtended extends Application_Admin_Area_Mode_Submode implements RecordEditScreenInterface
{
    use RecordSettingsScreenTrait;

    public function isEditMode() : bool
    {
        return true;
    }

    public function getDefaultAction() : string
    {
        return '';
    }
}
