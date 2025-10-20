<?php

use DBHelper\Admin\Traits\RecordListScreenInterface;
use DBHelper\Admin\Traits\RecordListScreenTrait;

abstract class Application_Admin_Area_Mode_CollectionList
    extends Application_Admin_Area_Mode
    implements RecordListScreenInterface
{
    use RecordListScreenTrait;
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
}
