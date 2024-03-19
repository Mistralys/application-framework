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

        $this->countries->clearIgnored();
    }

    protected function deleteAllCountries() : void
    {
        DBHelper::deleteRecords(Application_Countries::TABLE_NAME);
    }

    protected function createInvariantCountry() : Application_Countries_Country
    {
        $this->assertFalse($this->countries->isoExists(Application_Countries_Country::COUNTRY_INDEPENDENT_ISO));

        DBHelper::insertDynamic(
            Application_Countries::TABLE_NAME,
            array(
                Application_Countries_Country::COL_ISO => Application_Countries_Country::COUNTRY_INDEPENDENT_ISO,
                Application_Countries::PRIMARY_NAME => Application_Countries_Country::COUNTRY_INDEPENDENT_ID,
                Application_Countries_Country::COL_LABEL => 'Country independent'
            )
        );

        $country = $this->countries->getByID(Application_Countries_Country::COUNTRY_INDEPENDENT_ID);

        $this->assertTrue($country->isInvariant());

        return $country;
    }

    public function assertISOExists(string $iso) : void
    {
        $this->assertTrue($this->countries->isoExists($iso));
    }

    public function assertISONotExists(string $iso) : void
    {
        $this->assertFalse($this->countries->isoExists($iso));
    }
}
