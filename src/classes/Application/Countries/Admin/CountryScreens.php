<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Admin\BaseScreenRights;
use Application\Countries\Admin\Screens\BaseAreaScreen;
use Application\Countries\Admin\Screens\BaseCreateScreen;
use Application\Countries\Admin\Screens\BaseListScreen;
use Application\Countries\Rights\CountryScreenRights;

class CountryScreens extends BaseScreenRights
{
    public const SCREEN_AREA = BaseAreaScreen::class;
    public const SCREEN_LIST = BaseListScreen::class;
    public const SCREEN_CREATE = BaseCreateScreen::class;
    public const SCREEN_VIEW = '';

    public const SCREEN_RIGHTS = array(
        self::SCREEN_LIST => CountryScreenRights::SCREEN_LIST,
        self::SCREEN_CREATE => CountryScreenRights::SCREEN_CREATE,
        self::SCREEN_VIEW => CountryScreenRights::SCREEN_VIEW,
        self::SCREEN_AREA => CountryScreenRights::SCREEN_AREA,
    );

    protected function _registerRights(): void
    {
        foreach (self::SCREEN_RIGHTS as $screen => $right) {
            $this->register($screen, $right);
        }
    }
}
