<?php

declare(strict_types=1);

namespace TestDriver\Area;

use Application_Admin_Area;
use TestDriver;
use TestDriver\Area\RevisionableScreen\RevisionableListScreen;
use UI_Icon;

class RevisionableScreen extends Application_Admin_Area
{
    public const URL_NAME = 'revisionable';

    public function getDefaultMode(): string
    {
        return RevisionableListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return '';
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return TestDriver::icon()->revisionable();
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Revisionables');
    }

    public function getTitle(): string
    {
        return t('Revisionables');
    }
}
