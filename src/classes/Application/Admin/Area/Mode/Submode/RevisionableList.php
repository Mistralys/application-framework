<?php

abstract class Application_Admin_Area_Mode_Submode_RevisionableList extends Application_Admin_Area_Mode_Submode_Revisionable implements Application_Interfaces_Admin_RevisionableList
{
    use Application_Traits_Admin_RevisionableList;
    
    public function getDefaultAction()
    {
        return null;
    }
}

