<?php

abstract class Application_Admin_Area_Mode_Submode_CollectionDelete extends Application_Admin_Area_Mode_Submode
{
    use Application_Traits_Admin_CollectionDelete;
    
    public function getDefaultAction()
    {
        return null;
    }
}
