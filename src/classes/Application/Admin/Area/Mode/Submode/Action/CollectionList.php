<?php

use Application\Interfaces\Admin\CollectionListInterface;

abstract class Application_Admin_Area_Mode_Submode_Action_CollectionList
    extends Application_Admin_Area_Mode_Submode_Action
    implements CollectionListInterface
{
    use Application_Traits_Admin_CollectionList;
}
