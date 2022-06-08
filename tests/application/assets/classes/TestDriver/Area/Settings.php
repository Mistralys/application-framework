<?php
/**
 * @package TestDriver
 * @subpackage Administration
 */

declare(strict_types=1);

/**
 * @package TestDriver
 * @subpackage Administration
 */
class TestDriver_Area_Settings extends Application_Admin_Area_Settings
{
    public function getNavigationGroup() : string
    {
        return t('Manage');
    }
}
