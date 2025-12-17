<?php
/**
 * @package Admin
 * @subpackage Screens
 */

declare(strict_types=1);

namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * Interface for admin screen stubs: Any screen that implements this
 * interface will be ignored by the admin screen indexer.
 *
 * @package Admin
 * @subpackage Screens
 */
interface AdminScreenStubInterface extends AdminScreenInterface
{

}
