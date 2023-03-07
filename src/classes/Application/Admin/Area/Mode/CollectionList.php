<?php

use Application\Interfaces\Admin\CollectionListInterface;

abstract class Application_Admin_Area_Mode_CollectionList
    extends Application_Admin_Area_Mode
    implements CollectionListInterface
{
    use Application_Traits_Admin_CollectionList;
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
}
