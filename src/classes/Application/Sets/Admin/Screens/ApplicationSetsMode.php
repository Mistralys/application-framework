<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Sets\Admin\AppSetScreenRights;

class ApplicationSetsMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'appsets';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_APP_SETS;
    }

    public function getTitle(): string
    {
        return t('Application interface sets');
    }

    public function getNavigationTitle(): string
    {
        return t('Appsets');
    }

    public function getDevCategory(): string
    {
        return t('Settings');
    }

    public function getDefaultSubmode(): string
    {
        return SetsListSubmode::URL_NAME;
    }

    /**
     * @return class-string<SetsListSubmode>
     */
    public function getDefaultSubscreenClass(): string
    {
        return SetsListSubmode::class;
    }
}