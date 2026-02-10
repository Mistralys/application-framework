<?php
/**
 * @package TestDriver
 * @subpackage Code examples
 * @see \tests\TestDriver\CodeExamples\UI\QuickNavigationExample
 */

declare(strict_types=1);

namespace tests\TestDriver\CodeExamples\UI;

use Application_Admin_Area;
use Application\Users\Admin\Screens\UserSettingsArea;

/**
 * @package TestDriver
 * @subpackage Code examples
 */
// StartExample
abstract class QuickNavigationExample extends Application_Admin_Area
{
    protected function _handleQuickNavigation() : void
    {
        $this->quickNav->addURL(t('Regular link'), 'https://mistralys.eu');

        $this->quickNav->addURL(t('External link'), 'https://mistralys.eu')
            ->makeNewTab();

        $this->quickNav->addScreen(t('User settings'))
            ->setAreaID(UserSettingsArea::URL_NAME);
    }
}
// EndExample