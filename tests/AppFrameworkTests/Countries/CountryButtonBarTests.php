<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application;
use Application_Countries;
use Application_Countries_ButtonBar;
use Application_Countries_Country;
use Application_User;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

final class CountryButtonBarTests extends CountriesTestCase
{
    // region: _Tests

    public function test_defaultWithNoSelection() : void
    {
        $bar = $this->createBar();

        $this->assertNull($bar->getIDFromUser());
        $this->assertNull($bar->getIDFromRequest());

        $this->assertSame($this->defaultCountry, $bar->getCountry());
    }

    public function test_defaultFromRequest() : void
    {
        $bar = $this->createBar();

        $_REQUEST[Application_Countries_ButtonBar::REQUEST_PARAM_SELECT_COUNTRY] = $this->de->getID();

        $this->assertNotEmpty($bar->getCountryIDs());
        $this->assertNull($bar->getIDFromUser());
        $this->assertNotNull($bar->getIDFromRequest());

        $this->assertSame($this->de, $bar->getCountry());
    }

    public function test_getDefaultFromUser() : void
    {
        $this->user->setSetting(
            Application_Countries_ButtonBar::getStorageName(self::COUNTRY_BAR_ID),
            (string)$this->fr->getID()
        );

        $bar = $this->createBar();

        $this->assertNull($bar->getIDFromRequest());
        $this->assertNotNull($bar->getIDFromUser());

        $this->assertSame($this->fr, $bar->getCountry());
    }

    public function test_requestHasPrecedenceOverStorage() : void
    {
        $this->user->setSetting(
            Application_Countries_ButtonBar::getStorageName(self::COUNTRY_BAR_ID),
            (string)$this->es->getID()
        );

        $_REQUEST[Application_Countries_ButtonBar::REQUEST_PARAM_SELECT_COUNTRY] = $this->ca->getID();

        $bar = $this->createBar();

        $this->assertNotNull($bar->getIDFromRequest());
        $this->assertNotNull($bar->getIDFromUser());

        $this->assertSame($this->ca, $bar->getCountry());
    }

    public function test_selectedCountryIsSavedInUserSettings() : void
    {
        $this->assertNotSame($this->fr, $this->defaultCountry, 'The default country must not be the same as the test country');

        $bar = $this->createBar();

        $bar->selectCountry($this->fr);
        $this->assertSame($this->fr, $bar->getCountry());

        // Render the bar to ensure the settings are saved
        $bar->render();

        $newBar = $this->createBar();
        $this->assertSame($this->fr, $newBar->getCountry());
    }

    // endregion

    // region: Support methods

    private const COUNTRY_BAR_ID = 'unit-tests';

    /**
     * @var Application_Countries_Country[]
     */
    private array $allCountries = array();

    private Application_Countries_Country $defaultCountry;
    private Application_User $user;
    private Application_Countries_Country $ca;
    private Application_Countries_Country $de;
    private Application_Countries_Country $fr;
    private Application_Countries_Country $es;

    private function createBar() : Application_Countries_ButtonBar
    {
        return Application_Countries::createButtonBar(self::COUNTRY_BAR_ID, 'https://buttonbar.test/base');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ca = $this->countries->createNewCountry('ca', 'Canada');
        $this->de = $this->countries->createNewCountry('de', 'Germany');
        $this->fr = $this->countries->createNewCountry('fr', 'France');
        $this->es = $this->countries->createNewCountry('es', 'Spain');

        $_REQUEST = array();
        $_GET = array();
        $_POST = array();

        $this->user = Application::getUser();

        // Ensure that the user has no stored settings
        $this->user->resetSettings();

        $this->allCountries = $this->countries->getAll();

        $this->assertNotEmpty($this->allCountries);

        $this->defaultCountry = $this->allCountries[0];
    }

    // endregion
}
