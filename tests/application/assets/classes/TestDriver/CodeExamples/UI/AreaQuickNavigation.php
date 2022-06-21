<?php
/**
 * @package Test Application
 * @subpackage Code examples
 * @see \tests\TestDriver\CodeExamples\UI\AreaQuickNavigation
 */

declare(strict_types=1);

namespace tests\TestDriver\CodeExamples\UI;

use Application_Admin_Area;
use TestDriver_Area_Settings;
use UI_Page_Navigation;

/**
 * @package Test Application
 * @subpackage Code examples
 */
// StartExample
abstract class AreaQuickNavigation extends Application_Admin_Area
{
    protected function _handleQuickNavigation(UI_Page_Navigation $nav) : void
    {
        $nav->addURL(t('External link'), 'https://mistralys.eu');

        $nav->addInternalLink(TestDriver_Area_Settings::URL_NAME, t('User settings'));
    }
}
// EndExample