<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Countries;

use Application_Countries;
use Application_Countries_Country;
use AppLocalize\Localization\Countries\CountryCollection;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class UKGBTests extends CountriesTestCase
{
    public function test_isoExists_UK() : void
    {
        $this->createTestCountry('gb');

        $this->assertISOExists('GB');
        $this->assertISOExists('UK');
    }

    public function test_isoExists_GB() : void
    {
        $this->createTestCountry('uk');

        $this->assertTrue($this->countries->isoExists('GB'));
        $this->assertTrue($this->countries->isoExists('UK'));
    }

    public function test_getCountryByLocale_UK() : void
    {
        $this->createTestCountry('uk');

        $this->countries->getByISO('GB');
        $this->countries->getByISO('UK');

        $this->addToAssertionCount(1);
    }

    public function test_codeIsAdjustedInInstance() : void
    {
        $england = $this->createTestCountry('uk');

        $this->assertSame('gb', $england->getISO());
    }

    public function test_codeFromDatabaseIsAlsoAdjusted() : void
    {
        DBHelper::deleteRecords(Application_Countries::TABLE_NAME);

        DBHelper::insertDynamic(
            Application_Countries::TABLE_NAME,
            array(
                Application_Countries_Country::COL_ISO => 'uk',
                Application_Countries_Country::COL_LABEL => 'United Kingdom'
            )
        );

        $country = $this->countries->getByISO('uk');

        $this->assertSame('gb', $country->getISO());
    }

    public function test_filterCode() : void
    {
        $this->assertSame('gb', CountryCollection::getInstance()->filterCode('uk'));
    }

    /**
     * Using createNewRecord to bypass the automatic ISO alias mapping
     * must throw an exception.
     */
    public function test_bypassCreateMethod() : void
    {
        $this->expectExceptionCode(Application_Countries::ERROR_CANNOT_USE_ALIAS_FOR_CREATION);

        $this->countries->createNewRecord(array(
            Application_Countries_Country::COL_ISO => 'uk',
            Application_Countries_Country::COL_LABEL => 'United Kingdom'
        ));
    }

    /**
     * Using createNewRecord to bypass the automatic ISO alias mapping
     * must throw an exception.
     */
    public function test_createUnknownCountry() : void
    {
        $this->expectExceptionCode(Application_Countries::ERROR_UNKNOWN_ISO_CODE);

        $this->countries->createNewCountry('ug', 'Uganda');
    }
}
