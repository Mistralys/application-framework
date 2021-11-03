<?php

abstract class Application_Admin_Area_Mode_Submode_CollectionList extends Application_Admin_Area_Mode_Submode
{
    use Application_Traits_Admin_CollectionList;
    
    public function getDefaultAction() : string
    {
        return '';
    }
}

