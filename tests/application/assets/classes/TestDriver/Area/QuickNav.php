<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;
use tests\TestDriver\Admin\BaseArea;

class TestDriver_Area_QuickNav extends BaseArea
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

    protected function _handleQuickNavigation(UI_Page_Navigation $nav) : void
    {
        $nav->addExternalLink(t('External link'), 'https://mistralys.eu');

        $nav->addInternalLink(TestDriver_Area_Settings::URL_NAME, t('User settings'));

        $nav->addInternalLink(TestDriver_Area_Settings::URL_NAME, t('Active item'))->setActive();
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
            echo self::renderCodeExample(__DIR__.'/../CodeExamples/UI/AreaQuickNavigation.php');

        return OutputBuffering::get();
    }
}
