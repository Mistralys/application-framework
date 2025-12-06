<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * @package Application
 * @subpackage Admin
 */
interface ScreenRightsInterface
{
    /**
     * Fetches the right for a specific admin screen class.
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $screen
     * @return string
     */
    public function getByScreen(AdminScreenInterface|string $screen) : string;

    /**
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $screen
     * @return bool
     */
    public function screenExists(AdminScreenInterface|string $screen) : bool;

    /**
     * Fetches rights by admin screen class.
     * @return array<class-string<AdminScreenInterface>,string>
     */
    public function getAll() : array;
}
