<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

use DBHelper\Admin\Traits\CollectionRecordScreenInterface;
use DBHelper\Admin\Traits\CollectionRecordScreenTrait;

/**
 * Abstract base class for an admin "action" screen that works with
 * a DBHelper collection record. It has methods to load the
 * record automatically from the request.
 *
 * @package DBHelper
 * @subpackage Admin
 */
abstract class Application_Admin_Area_Mode_Submode_Action_CollectionRecord
    extends Application_Admin_Area_Mode_Submode_Action
    implements CollectionRecordScreenInterface
{
    use CollectionRecordScreenTrait;
}
