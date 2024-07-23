<?php
/**
 * @package Application
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\Interfaces\Admin;

/**
 * Interface for screens that can provide a URL to
 * redirect to in case the target record is missing.
 *
 * @package Application
 * @subpackage Admin Screens
 */
interface MissingRecordInterface
{
    public function getRecordMissingURL(): string;
}
