<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application\AppFactory;
use Application_Countries;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

final class IgnoreTests extends CountriesTestCase
{
    /**
     * When a country is ignored, it should no longer be
     * included in the list of all countries, and the
     *
     */
    public function test_setIgnored() : void
    {
        $this->countries->createNewCountry(Application_Countries::COUNTRY_FR, 'France');
        $this->countries->createNewCountry(Application_Countries::COUNTRY_PL, 'Poland');

        $this->assertCount(2, $this->countries->getAll());

        $this->countries->setCountryIgnored(Application_Countries::COUNTRY_PL);

        $this->assertCount(1, $this->countries->getAll());
        $this->assertISONotExists(Application_Countries::COUNTRY_PL);
        $this->assertISOExists(Application_Countries::COUNTRY_FR);
    }

    /**
     * An ignored country should still be accessible by its ISO code,
     * even if its is excluded from the list of all countries.
     */
    public function test_getByISO() : void
    {
        $this->countries->createNewCountry(Application_Countries::COUNTRY_FR, 'France');
        $this->countries->createNewCountry(Application_Countries::COUNTRY_PL, 'Poland');

        $pl = $this->countries->getByISO(Application_Countries::COUNTRY_PL);

        $this->assertSame('Poland', $pl->getLabel());
    }

    public function test_languagesAreAlsoIgnored() : void
    {
        $this->countries->createNewCountry(Application_Countries::COUNTRY_FR, 'France');
        $this->countries->createNewCountry(Application_Countries::COUNTRY_PL, 'Poland');

        $languages = AppFactory::createLanguages();

        $this->assertCount(2, $this->countries->getAll());
        $this->assertCount(2, $languages->getAll());

        $this->countries->setCountryIgnored(Application_Countries::COUNTRY_PL);

        $this->assertCount(1, $languages->getAll());

        $this->countries->clearIgnored();

        $this->assertCount(2, $languages->getAll());
    }

    public function test_localesAreAlsoIgnored() : void
    {
        $this->countries->createNewCountry(Application_Countries::COUNTRY_FR, 'France');
        $this->countries->createNewCountry(Application_Countries::COUNTRY_PL, 'Poland');

        $locales = AppFactory::createLocales();

        $this->assertCount(2, $this->countries->getAll());
        $this->assertCount(2, $locales->getAll());

        $this->countries->setCountryIgnored(Application_Countries::COUNTRY_PL);

        $this->assertCount(1, $locales->getAll());

        $this->countries->clearIgnored();

        $this->assertCount(2, $locales->getAll());
    }
}
