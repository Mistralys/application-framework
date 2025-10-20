<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application_Admin_Area_Mode_Submode;
use DBHelper\Admin\Traits\CollectionRecordScreenInterface;
use DBHelper\Admin\Traits\CollectionRecordScreenTrait;

/**
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordScreen
    extends Application_Admin_Area_Mode_Submode
    implements CollectionRecordScreenInterface
{
    use CollectionRecordScreenTrait;
}
