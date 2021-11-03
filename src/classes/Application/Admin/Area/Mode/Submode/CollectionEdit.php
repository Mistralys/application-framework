<?php

abstract class Application_Admin_Area_Mode_Submode_CollectionEdit extends Application_Admin_Area_Mode_Submode implements Application_Interfaces_Admin_CollectionSettings
{
    use Application_Traits_Admin_CollectionSettings;

    abstract public function isUserAllowedEditing() : bool;

    /**
     * Whether the record can be edited at all.
     *
     * @return bool
     */
    abstract public function isEditable() : bool;

    public function isEditMode() : bool
    {
        return true;
    }

    public function getDefaultAction() : string
    {
        return '';
    }
}
