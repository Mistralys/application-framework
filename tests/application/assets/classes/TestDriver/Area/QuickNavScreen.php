<?php
/**
 * @package TestDriver
 * @subpackage Administration
 * @see \TestDriver\Area\QuickNavScreen
 */

declare(strict_types=1);

namespace TestDriver\Area;

use Application_Admin_Area;
use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_Appinterface;
use AppUtils\OutputBuffering;
use tests\TestDriver\Admin\BaseArea;

/**
 * Abstract base class for navigation items in the quick navigation.
 *
 * @package TestDriver
 * @subpackage Administration
 */
class QuickNavScreen extends BaseArea
{
    public const URL_NAME = 'quicknav';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode() : string
    {
        return '';
    }

    public function getNavigationGroup() : string
    {
        return '';
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function getDependencies() : array
    {
        return array();
    }

    public function isCore() : bool
    {
        return true;
    }

    public function getNavigationTitle() : string
    {
        return t('QuickNav');
    }

    public function getTitle() : string
    {
        return t('Quick navigation');
    }

    protected function _handleQuickNavigation() : void
    {
        $this->quickNav->addScreen(t('Quick navigation'))
            ->setAreaID(self::URL_NAME);

        $this->quickNav->addScreen(t('Interface refs'))
            ->setAreaID(Application_Admin_Area_Devel::URL_NAME)
            ->setModeID(Application_Admin_Area_Devel_Appinterface::URL_NAME);

        $this->quickNav->addURL(t('External link'), 'https://mistralys.eu')
            ->makeNewTab();
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->setTitle($this->getTitle())
            ->appendContent($this->renderBody())
            ->makeWithoutSidebar();
    }

    private function renderBody() : string
    {
        OutputBuffering::start();

        ?>
        <p>
            <?php
            pts('This showcases the quick navigation, which area classes can set up.');
            pts('It is typically used to provide shortcuts to sub-screens of the administration area.');
            pts('Both internal and external links are supported.');
            ?>
        </p>
        <p>
            <b><?php pt('Usage') ?></b>
        </p>
        <p>
            <?php
            $callback = array(Application_Admin_Area::class, '_handleQuickNavigation');
            pts(
                'The area class must implement the method %1$s, and add the relevant links there.',
                $callback[1]
            );
            pts(
                'The navigation is shown if one ore more items are valid (%1$s).',
                t('If they fulfill all conditions they may have been given')
            );
            ?>
        </p>
        <?php
        echo self::renderCodeExample(__DIR__ . '/../CodeExamples/UI/QuickNavigationExample.php');

        return OutputBuffering::get();
    }
}
