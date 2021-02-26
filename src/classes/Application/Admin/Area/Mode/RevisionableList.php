<?php

abstract class Application_Admin_Area_Mode_RevisionableList extends Application_Admin_Area_Mode implements Application_Interfaces_Admin_RevisionableList
{
    use Application_Traits_Admin_RevisionableList;
    
    public function getDefaultSubmode()
    {
        return null;
    }
}