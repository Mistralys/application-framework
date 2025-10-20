<?php

declare(strict_types=1);

use DBHelper\Admin\Traits\CollectionRecordScreenInterface;
use DBHelper\Admin\Traits\CollectionRecordScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_CollectionRecord
    extends Application_Admin_Area_Mode_Submode
    implements CollectionRecordScreenInterface
{
    use CollectionRecordScreenTrait;
}
