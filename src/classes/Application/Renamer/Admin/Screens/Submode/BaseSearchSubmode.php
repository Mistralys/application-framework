<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\Development\DevScreenRights;
use Application\Renamer\Index\RenamerIndexRunner;
use Application\Renamer\RenamingManager;

abstract class BaseSearchSubmode extends BaseSubmode
{
    public const string URL_NAME = 'search';

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
