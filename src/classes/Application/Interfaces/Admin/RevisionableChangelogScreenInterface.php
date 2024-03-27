<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application\Traits\Admin\RevisionableChangelogScreenTrait;
use Application_Admin_ScreenInterface;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionableChangelogScreenTrait
 */
interface RevisionableChangelogScreenInterface extends Application_Admin_ScreenInterface
{
    public const REVISIONABLE_CHANGELOG_ERROR_NOT_A_VALID_REVISIONABLE = 630001;
}
