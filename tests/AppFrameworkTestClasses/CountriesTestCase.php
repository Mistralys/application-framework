<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Languages;
use Application_Countries;
use Application_Countries_Country;
use DBHelper;

/**
 * @package Application
 * @subpackage UnitTests
 */
abstract class CountriesTestCase extends ApplicationTestCase
{
    protected Application_Countries $countries;

    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
        $this->deleteAllCountries();

        $this->countries = Application_Countries::getInstance();

        $this->countries->resetCollection();
        $this->countries->clearIgnored();
    }

    protected function deleteAllCountries() : void
    {
        DBHelper::deleteRecords(Application_Countries::TABLE_NAME);
    }

    protected function createInvariantCountry() : Application_Countries_Country
    {
        $country = $this->countries->createInvariantCountry();

        $this->assertTrue($country->isInvariant());

        return $country;
    }

    public function assertISOExists(string $iso) : void
    {
        $this->assertTrue(
            $this->countries->isoExists($iso),
            'ISO code "'.$iso.'" does not exist.'.PHP_EOL.
            'Available codes: '.implode(', ', $this->countries->getSupportedISOs())
        );
    }

    public function assertISONotExists(string $iso) : void
    {
        $this->assertFalse($this->countries->isoExists($iso));
    }
}
