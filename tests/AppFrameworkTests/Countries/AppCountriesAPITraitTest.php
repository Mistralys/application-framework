<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\AppFactory;
use Application\Countries\API\AppCountriesAPIInterface;
use Application_Countries;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryFR;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestGetCountriesAPI;
use TestDriver\API\TestGetCountriesBySetAPI;

/**
 * Integration tests for {@see AppCountriesAPIInterface} / {@see \Application\Countries\API\AppCountriesAPITrait}.
 *
 * Covers the full multi-country parameter stack: individual IDs/ISOs, OrRule
 * mutual exclusivity, validation errors, ISO case insensitivity, and manual
 * pre-selection.
 *
 * @see TestGetCountriesAPI
 * @see TestGetCountriesBySetAPI
 */
final class AppCountriesAPITraitTest extends APITestCase
{
    // -----------------------------------------------------------------------
    // region: TestGetCountriesAPI (individual params, no OrRule)
    // -----------------------------------------------------------------------

    public function test_methodIsValidWithNoParams(): void
    {
        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse(
            $method,
            'Method must be valid when no country params are provided — none are mandatory.'
        );

        $this->assertSame(
            array(),
            $method->manageAppCountriesParams()->resolveValue(),
            'Resolved value must be an empty array when no params are provided.'
        );
    }

    public function test_methodResolvesWithSingleID(): void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_IDS] = $country->getID();

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(1, $resolved, 'Expected exactly one country to be resolved.');
        $this->assertSame($country, $resolved[0]);
    }

    public function test_methodResolvesWithMultipleIDs(): void
    {
        $countryDE = $this->createTestCountry(CountryDE::ISO_CODE);
        $countryFR = $this->createTestCountry(CountryFR::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_IDS] = implode(',', array(
            $countryDE->getID(),
            $countryFR->getID(),
        ));

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(2, $resolved, 'Expected exactly two countries to be resolved.');
        $this->assertSame($countryDE, $resolved[0]);
        $this->assertSame($countryFR, $resolved[1]);
    }

    public function test_methodResolvesWithSingleISO(): void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = $country->getISO();

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(1, $resolved, 'Expected exactly one country to be resolved.');
        $this->assertSame($country, $resolved[0]);
    }

    public function test_methodResolvesWithMultipleISOs(): void
    {
        $countryDE = $this->createTestCountry(CountryDE::ISO_CODE);
        $countryFR = $this->createTestCountry(CountryFR::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = implode(',', array(
            $countryDE->getISO(),
            $countryFR->getISO(),
        ));

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(2, $resolved, 'Expected exactly two countries to be resolved.');
        $this->assertSame($countryDE, $resolved[0]);
        $this->assertSame($countryFR, $resolved[1]);
    }

    public function test_methodInvalidWithInvalidID(): void
    {
        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_IDS] = 999999;

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertErrorResponseCode(
            $method,
            APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS
        );
    }

    public function test_methodInvalidWithInvalidISO(): void
    {
        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = 'xx';

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertErrorResponseCode(
            $method,
            APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS
        );
    }

    public function test_isoResolutionIsCaseInsensitive(): void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        // Provide upper-case ISO code — should still resolve correctly.
        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = strtoupper($country->getISO());

        $method = new TestGetCountriesAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(1, $resolved, 'Expected one country resolved from upper-case ISO code.');
        $this->assertSame($country, $resolved[0]);
    }

    public function test_manualSelectAppCountries(): void
    {
        $countryDE = $this->createTestCountry(CountryDE::ISO_CODE);
        $countryFR = $this->createTestCountry(CountryFR::ISO_CODE);

        $method = new TestGetCountriesAPI(APIManager::getInstance());
        $method->manageAppCountriesParams()->selectAppCountries(array($countryDE, $countryFR));

        $this->assertSuccessfulResponse($method);

        $resolved = $method->manageAppCountriesParams()->resolveValue();

        $this->assertCount(2, $resolved, 'Expected two manually pre-selected countries.');
        $this->assertContains($countryDE, $resolved);
        $this->assertContains($countryFR, $resolved);
    }

    // -----------------------------------------------------------------------
    // region: TestGetCountriesBySetAPI (OrRule mutual exclusivity)
    // -----------------------------------------------------------------------

    public function test_setIsInvalidWithNoCountryParams(): void
    {
        $method = new TestGetCountriesBySetAPI(APIManager::getInstance());

        $this->assertErrorResponseCode(
            $method,
            APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS
        );
    }

    public function test_setIsValidWithCountryIDs(): void
    {
        $countryDE = $this->createTestCountry(CountryDE::ISO_CODE);
        $countryFR = $this->createTestCountry(CountryFR::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_IDS] = implode(',', array(
            $countryDE->getID(),
            $countryFR->getID(),
        ));

        $method = new TestGetCountriesBySetAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $countries = $method->getCountries();

        $this->assertCount(2, $countries, 'Expected two countries resolved from IDs.');
        $this->assertSame($countryDE, $countries[0]);
        $this->assertSame($countryFR, $countries[1]);
    }

    public function test_setIsValidWithCountryISOs(): void
    {
        $countryDE = $this->createTestCountry(CountryDE::ISO_CODE);
        $countryFR = $this->createTestCountry(CountryFR::ISO_CODE);

        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = implode(',', array(
            $countryDE->getISO(),
            $countryFR->getISO(),
        ));

        $method = new TestGetCountriesBySetAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $countries = $method->getCountries();

        $this->assertCount(2, $countries, 'Expected two countries resolved from ISOs.');
        $this->assertSame($countryDE, $countries[0]);
        $this->assertSame($countryFR, $countries[1]);
    }

    public function test_setIDsWinWhenBothAreProvided(): void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        // OrRule is first-match-wins: IDs are registered first, so IDs win
        // when both params are provided. This is not an error.
        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_IDS] = $country->getID();
        $_REQUEST[AppCountriesAPIInterface::PARAM_COUNTRY_ISOS] = $country->getISO();

        $method = new TestGetCountriesBySetAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse(
            $method,
            'OrRule must succeed with IDs winning when both params are provided.'
        );

        $countries = $method->getCountries();
        $this->assertCount(1, $countries, 'Expected exactly one country resolved via the IDs set.');
        $this->assertSame($country, $countries[0]);
    }

    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        $this->cleanUpTables(array(Application_Countries::TABLE_NAME));

        // Reset the in-memory country collection cache so that the
        // freshly-cleaned table is re-read from the DB in each test.
        AppFactory::createCountries()->resetCollection();
    }
}
