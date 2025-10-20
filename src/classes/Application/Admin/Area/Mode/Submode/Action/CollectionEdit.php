<?php

declare(strict_types=1);

use DBHelper\Admin\Traits\RecordEditScreenInterface;
use DBHelper\Admin\Traits\RecordEditScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_Action_CollectionEdit
    extends Application_Admin_Area_Mode_Submode_Action
    implements
    RecordEditScreenInterface
{
    use Application_Traits_Admin_CollectionSettings;
    use RecordEditScreenTrait;
}
