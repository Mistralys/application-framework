<?php

declare(strict_types=1);

namespace Application\Maintenance\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Maintenance\Admin\MaintenanceScreenRights;

class MaintenanceMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'maintenance';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MaintenanceScreenRights::SCREEN_MAIN;
    }

    public function getNavigationTitle(): string
    {
        return t('Maintenance');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    public function getTitle(): string
    {
        return t('Planned maintenance');
    }

    public function getDefaultSubmode(): string
    {
        return ListSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ListSubmode::class;
    }
}
