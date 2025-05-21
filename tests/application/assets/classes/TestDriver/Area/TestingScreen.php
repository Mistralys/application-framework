<?php

declare(strict_types=1);

namespace TestDriver\Area;

use Application_Admin_Area;
use TestDriver\Area\TestingScreen\TestingOverviewScreen;
use UI;
use UI_Icon;

class TestingScreen extends Application_Admin_Area
{
    public const URL_NAME = 'testing';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode(): string
    {
        return TestingOverviewScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return false;
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->developer();
    }

    public function getNavigationTitle(): string
    {
        return t('Testing');
    }

    public function getTitle(): string
    {
        return t('Testing');
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->developer());

        $sub = $this->getActiveSubscreen();
        if(isset($sub)) {
            $this->renderer->setSubtitle(sb()
                ->t('Test:')
                ->add($sub->getTitle())
            );
        }
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }
}
