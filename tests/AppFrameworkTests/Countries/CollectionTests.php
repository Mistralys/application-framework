<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Countries;

use Application\Countries\CountriesCollection;
use Application_Countries_Country;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Country\CountryFI;
use AppLocalize\Localization\Country\CountryZZ;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class CollectionTests extends CountriesTestCase
{
    public function test_create() : void
    {
        $this->createTestCountry('de');
        $this->createTestCountry('fr');

        $collection = $this->countries->getCollection();

        $this->assertCount(2, $collection->getAll());
        $this->assertCount(2, $collection->getIDs());
        $this->assertCount(2, $collection->getISOs());
    }

    public function test_notHasInvariant() : void
    {
        $collection = $this->countries->getCollection();

        $this->assertFalse($collection->hasInvariant());
    }

    public function test_hasInvariant() : void
    {
        $invariant = $this->createInvariantCountry();

        $collection = $this->countries->getCollection();

        $this->assertContains($invariant->getISO(), $collection->getISOs(), print_r($collection->getISOs(), true));
        $this->assertContains($invariant->getID(), $collection->getIDs());
        $this->assertContains($invariant, $collection->getAll());

        $this->assertTrue($collection->hasInvariant());
        $this->assertTrue($collection->hasID($invariant->getID()));
        $this->assertTrue($collection->hasISO($invariant->getISO()));
    }

    public function test_ignoreInvariant() : void
    {
        $invariant = $this->createInvariantCountry();

        $collection = $this->countries->getCollection()
            ->excludeInvariant();

        $this->assertNotContains($invariant->getISO(), $collection->getISOs());
        $this->assertNotContains($invariant->getID(), $collection->getIDs());
        $this->assertNotContains($invariant, $collection->getAll());

        $this->assertFalse($collection->hasInvariant());
        $this->assertFalse($collection->hasISO($invariant->getISO()));
        $this->assertFalse($collection->hasID($invariant->getID()));
    }

    public function test_addInstance() : void
    {
        $collection = CountriesCollection::create();

        $de = $this->createTestCountry('de');

        $collection->addCountry($de);

        $this->assertTrue($collection->hasCountry($de));
    }

    public function test_addID() : void
    {
        $collection = CountriesCollection::create();

        $de = $this->createTestCountry('de');

        $collection->addID($de->getID());

        $this->assertTrue($collection->hasCountry($de));
    }

    public function test_addISO() : void
    {
        $collection = CountriesCollection::create();

        $de = $this->createTestCountry('de');

        $collection->addISO($de->getISO());

        $this->assertTrue($collection->hasCountry($de));
    }

    /**
     * Test used to verify a bug where the instances were different.
     * @link https://github.com/Mistralys/application-framework/issues/37
     */
    public function test_consistentInstances() : void
    {
        $this->createTestCountry('gb');

        $this->countries->resetCollection();

        $countryA = $this->countries->getByISO('gb');
        $countryB = $this->countries->getByID($countryA->getID());

        $this->assertSame($countryA, $countryB);
    }

    public function test_getLocale() : void
    {
        $at = $this->createTestCountry('at');

        $locale = $at->getLocale();

        $this->assertSame('de_AT', $locale->getCode());
    }

    public function test_getInvariantCountry() : void
    {
        $this->createInvariantCountry();

        $this->assertSame(
            Application_Countries_Country::COUNTRY_INDEPENDENT_ISO,
            $this->countries->getInvariantCountry()->getISO()
        );
    }

    public function test_resolveCountry() : void
    {
        $test = $this->createTestCountry(CountryFI::ISO_CODE);

        $this->assertSame($test, $this->countries->resolveCountry($test->getISO()));
        $this->assertSame($test, $this->countries->resolveCountry($test->getID()));
        $this->assertSame($test, $this->countries->resolveCountry($test));
        $this->assertSame($test, $this->countries->resolveCountry(CountryCollection::getInstance()->choose()->fi()));
    }
}
