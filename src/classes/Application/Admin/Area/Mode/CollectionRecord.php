<?php

declare(strict_types=1);

use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

abstract class Application_Admin_Area_Mode_CollectionRecord
    extends Application_Admin_Area_Mode
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
