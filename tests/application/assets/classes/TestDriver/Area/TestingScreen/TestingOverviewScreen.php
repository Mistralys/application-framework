<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application_Admin_Area_Mode;
use Application_Admin_ScreenInterface;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;

class TestingOverviewScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'overview';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Overview');
    }

    protected function _renderContent()
    {
        $list = $this->ui->createBigSelection()
            ->makeSmall();

        $list->addLink(
            CollectionCreateBasicScreen::getTestLabel(),
            $this->getTestURL(CollectionCreateBasicScreen::URL_NAME)
        );

        $list->addLink(
            CollectionCreateManagerLegacyScreen::getTestLabel(),
            $this->getTestURL(CollectionCreateManagerLegacyScreen::URL_NAME)
        );

        $list->addLink(
            CollectionCreateManagerExtendedScreen::getTestLabel(),
            $this->getTestURL(CollectionCreateManagerExtendedScreen::URL_NAME)
        );

        return $this->renderer
            ->appendContent($list)
            ->makeWithoutSidebar();
    }

    protected function getTestURL(string $testURLName, array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = TestingScreen::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = $testURLName;

        return ClassFactory::createRequest()->buildURL($params);
    }
}
