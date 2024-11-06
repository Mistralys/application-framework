<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Countries;

use Application\Countries\CountriesCollection;
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
}
