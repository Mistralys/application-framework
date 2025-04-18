<?php

declare(strict_types=1);

namespace UI\AppLauncher\Apps;

use UI;
use UI\AppLauncher\BaseLauncherApp;
use UI_Icon;

class MediaLibraryApp extends BaseLauncherApp
{
    public const APP_ID = 'MediaLibrary';
    public const LOGO_IMAGE = 'media-library.png';

    public function getID(): string
    {
        return self::APP_ID;
    }

    public function getIcon(): ?UI_Icon
    {
        return UI::icon()->media();
    }

    protected function _getLogoFileName(): string
    {
        return self::LOGO_IMAGE;
    }

    public function getLabel(): string
    {
        return t('Media Library');
    }

    public function getDescription(): string
    {
        return t('Manage your media files and libraries.');
    }
}
