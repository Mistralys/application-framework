<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application_Admin_Area_Mode_Submode_Action;
use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

/**
 * Abstract base class for an admin "action" screen that works with
 * a DBHelper collection record. It has methods to load the
 * record automatically from the request.
 *
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordAction
    extends Application_Admin_Area_Mode_Submode_Action
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
