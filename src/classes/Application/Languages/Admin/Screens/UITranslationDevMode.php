<?php

declare(strict_types=1);

namespace Application\Languages\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Application;
use Application\Development\Admin\DevScreenRights;
use AppLocalize\Localization;
use UI;
use UI_Icon;
use function AppLocalize\tex;

class UITranslationDevMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'translations';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return t('UI Translation tools');
    }

    public function getNavigationTitle(): string
    {
        return t('Translation');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->translation();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function renderContent(): string
    {
        $editor = Localization::createEditor();
        $editor->addRequestParam('page', $this->getURLName());
        $editor->setAppName(tex('%1$s translations', 'Placeholder contains application name.', $this->driver->getAppNameShort()));
        $editor->setBackURL(APP_URL, t('Back to %1$s', $this->driver->getAppNameShort()));
        $editor->display();

        Application::exit('Translation editor finished');
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_TRANSLATIONS;
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }
}
