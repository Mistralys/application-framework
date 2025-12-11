<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Renamer\Admin\RenamerScreenRights;
use Application\Renamer\Admin\Screens\Submode\ConfigurationSubmode;
use UI;

class RenamerMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'renamer';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return RenamerScreenRights::SCREEN_RENAMER;
    }

    public function getNavigationTitle(): string
    {
        return t('DB Renamer');
    }

    public function getTitle(): string
    {
        return t('Database Renamer');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    public function getDefaultSubmode(): string
    {
        return ConfigurationSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ConfigurationSubmode::class;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->text());
    }
}
