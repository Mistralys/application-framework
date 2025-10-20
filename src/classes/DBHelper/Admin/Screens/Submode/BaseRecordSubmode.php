<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

/**
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordSubmode
    extends Application_Admin_Area_Mode_Submode
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
