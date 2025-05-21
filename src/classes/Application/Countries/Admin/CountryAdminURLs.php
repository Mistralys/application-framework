<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\Countries\Admin\Screens\BaseAreaScreen;
use Application\Countries\Admin\Screens\BaseViewScreen;
use Application\Countries\Admin\Screens\View\BaseSettingsScreen;
use Application\Countries\Admin\Screens\View\BaseStatusScreen;
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
            ->submode(BaseStatusScreen::URL_NAME);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->view()
            ->submode(BaseSettingsScreen::URL_NAME);
    }

    public function view() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseAreaScreen::URL_NAME)
            ->mode(BaseViewScreen::URL_NAME)
            ->int(Application_Countries::REQUEST_PARAM_ID, $this->country->getID());
    }
}
