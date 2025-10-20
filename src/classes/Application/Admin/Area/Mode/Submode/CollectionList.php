<?php

use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_CollectionList
    extends Application_Admin_Area_Mode_Submode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;
    
    public function getDefaultAction() : string
    {
        return '';
    }
}

