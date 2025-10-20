<?php

use DBHelper\Admin\Traits\RecordDeleteScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_CollectionDelete extends Application_Admin_Area_Mode_Submode
{
    use RecordDeleteScreenTrait;
    
    public function getDefaultAction() : string
    {
        return '';
    }
}
