<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use DBHelper\Admin\Traits\RecordScreenInterface;
use DBHelper\Admin\Traits\RecordScreenTrait;

/**
 * @package DBHelper
 * @subpackage Admin
 */
abstract class BaseRecordSubmode
    extends BaseSubmode
    implements RecordScreenInterface
{
    use RecordScreenTrait;
}
