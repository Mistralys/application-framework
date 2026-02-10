<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\CountriesArea;
use Application\Countries\Admin\Screens\Mode\ViewScreen;
use Application\Countries\Admin\Screens\Mode\View\SettingsScreen;
use Application\Countries\Admin\Screens\Mode\View\StatusScreen;
use Application_Countries;
use Application_Countries_Country;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class CountryAdminURLs
{
    private Application_Countries_Country $country;

    public function __construct(Application_Countries_Country $country)
    {
        $this->country = $country;
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->view()
            ->submode(StatusScreen::URL_NAME);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->view()
            ->submode(SettingsScreen::URL_NAME);
    }

    public function view() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(CountriesArea::URL_NAME)
            ->mode(ViewScreen::URL_NAME)
            ->int(Application_Countries::REQUEST_PARAM_ID, $this->country->getID());
    }
}
