<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Admin\BaseScreenRights;
use Application\Countries\Admin\Screens\CountriesArea;
use Application\Countries\Admin\Screens\Mode\CreateScreen;
use Application\Countries\Admin\Screens\Mode\ListScreen;
use Application\Countries\Rights\CountryScreenRights;

class CountryScreens extends BaseScreenRights
{
    public const SCREEN_AREA = CountriesArea::class;
    public const SCREEN_LIST = ListScreen::class;
    public const SCREEN_CREATE = CreateScreen::class;
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
