<?php

declare(strict_types=1);

namespace Application\ErrorLog\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\ErrorLog\Admin\ErrorLogScreenRights;

class ErrorLogMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'errorlog';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return ErrorLogScreenRights::SCREEN_MAIN;
    }

    public function getTitle(): string
    {
        return t('Error log');
    }

    public function getNavigationTitle(): string
    {
        return $this->getTitle();
    }

    public function getDevCategory(): string
    {
        return t('Logs');
    }

    public function getDefaultSubmode(): string
    {
        return ListSubmode::URL_NAME;
    }

    /**
     * @return class-string<ListSubmode>
     */
    public function getDefaultSubscreenClass(): string
    {
        return ListSubmode::class;
    }
}
