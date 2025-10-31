<?php
/**
 * @package AppFrameworkTestClasses
 * @subpackage Admin
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Admin;

use Application_Admin_Area_Mode_Submode_Action;
use Application_Interfaces_Admin_ScreenDisplayMode;
use Application_Traits_Admin_ScreenDisplayMode;

/**
 * A stub admin screen for testing purposes that implements
 * display modes using the trait {@see Application_Traits_Admin_ScreenDisplayMode}.
 *
 * @package AppFrameworkTestClasses
 * @subpackage Admin
 */
final class DisplayModeScreenStub extends Application_Admin_Area_Mode_Submode_Action implements Application_Interfaces_Admin_ScreenDisplayMode
{
    use Application_Traits_Admin_ScreenDisplayMode;

    public function getURLName(): string
    {
        return 'stub-display-mode-screen';
    }

    public function getNavigationTitle(): string
    {
        return 'Display Mode Screen Stub';
    }

    public function getTitle(): string
    {
        return 'Display Mode Screen Stub';
    }

    public function resolveDisplayMode(): string
    {
        return 'custom-mode';
    }

    public function getDefaultDisplayMode(): string
    {
        return 'default-mode';
    }
}
