<?php

use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_CollectionCreate extends Application_Admin_Area_Mode_Submode implements Application_Interfaces_Admin_CollectionCreate
{
    use RecordSettingsScreenTrait;

    public function isUserAllowedEditing() : bool
    {
        return $this->isUserAllowed();
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