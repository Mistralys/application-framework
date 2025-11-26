<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Mode;

use Application\Development\DevScreenRights;
use Application\Renamer\Admin\Screens\Submode\BaseConfigurationSubmode;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode;
use UI;

abstract class BaseRenamerMode extends Application_Admin_Area_Mode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'renamer';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RENAMER;
    }

    public function getNavigationTitle(): string
    {
        return t('DB Renamer');
    }

    public function getTitle(): string
    {
        return t('Database Renamer');
    }

    public function getDefaultSubmode(): string
    {
        return BaseConfigurationSubmode::URL_NAME;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->text());
    }
}
