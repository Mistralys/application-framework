<?php

abstract class UI_Page_Sidebar_LockableItem
    extends UI_Page_Sidebar_Item
    implements Application_LockableItem_Interface
{
   use Application_Traits_LockableItem;
   use Application_Traits_LockableStatus;
}
