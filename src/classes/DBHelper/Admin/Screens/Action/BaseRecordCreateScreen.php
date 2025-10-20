<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateScreen extends Application_Admin_Area_Mode_Submode_Action implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;

    public function isUserAllowedEditing(): bool
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
