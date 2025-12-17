<?php

declare(strict_types=1);

namespace Application\Admin\Welcome\Screens;

use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\OfflineEvents\WelcomeQuickNavEvent;
use tests\TestDriver\Admin\BaseArea;
use UI;
use UI_Icon;

class WelcomeArea extends BaseArea implements ClassLoaderScreenInterface
{
    public const string URL_NAME = 'welcome';

    public function getDefaultMode(): string
    {
        return OverviewMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return OverviewMode::class;
    }

    public function getNavigationGroup(): string
    {
        return '';
    }

    public function getRequiredRight(): null
    {
        return null;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return false;
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return t('Quickstart');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->home();
    }

    protected function _handleQuickNavigation(): void
    {
        AppFactory::createOfflineEvents()->triggerEvent(
            WelcomeQuickNavEvent::EVENT_NAME,
            array($this, $this->quickNav)
        );
    }
}
