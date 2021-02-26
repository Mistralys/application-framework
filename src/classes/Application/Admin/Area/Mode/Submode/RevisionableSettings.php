<?php

abstract class Application_Admin_Area_Mode_Submode_RevisionableSettings extends Application_Admin_Area_Mode_Submode_Revisionable
{
    use Application_Admin_RevisionableSettings;
    
    public function getDefaultAction()
    {
        return null;
    }
}