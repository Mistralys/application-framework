<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application\Development\DevScreenRights;
use Application\Renamer\Index\RenamerIndexRunner;
use Application\Renamer\RenamingManager;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;

abstract class BaseSearchSubmode extends Application_Admin_Area_Mode_Submode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'search';

    private RenamerIndexRunner $runner;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RENAMER_SEARCH;
    }

    public function getNavigationTitle(): string
    {
        return t('Search');
    }

    public function getTitle(): string
    {
        return t('Search');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        self::createSearchRunner()->indexResults();

        $this->redirectTo(RenamingManager::getInstance()->adminURL()->results());
    }

    public static function createSearchRunner() : RenamerIndexRunner
    {
        return RenamingManager::getInstance()->createSearchRunner(BaseConfigurationSubmode::requireConfig());
    }
}
