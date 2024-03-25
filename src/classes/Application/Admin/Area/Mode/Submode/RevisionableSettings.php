<?php

declare(strict_types=1);

abstract class Application_Admin_Area_Mode_Submode_RevisionableSettings extends Application_Admin_Area_Mode_Submode_Revisionable
{
    use RevisionableSettingsTrait;
    
    public function getDefaultAction() : string
    {
        return '';
    }
}
