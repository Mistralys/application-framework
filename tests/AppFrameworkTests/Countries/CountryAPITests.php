<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use application\assets\classes\TestDriver\API\TestGetCountryBySetAPI;
use Application\Countries\API\AppCountryAPIInterface;
use AppLocalize\Localization\Country\CountryDE;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestGetCountryAPI;

final class CountryAPITests extends APITestCase
{
    public function test_setIsInvalidIfNoCountryParams() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryBySetAPI::METHOD_NAME;

        $method = new TestGetCountryBySetAPI(APIManager::getInstance());

        $this->assertErrorResponseCode($method, APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS);
    }

    public function test_setIsValidWithCountryID() : void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryBySetAPI::METHOD_NAME;
        $_REQUEST[AppCountryAPIInterface::KEY_COUNTRY_ID] = $country->getID();

        $method = new TestGetCountryBySetAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $this->assertSame($country, $method->getCountry());
    }

    public function test_setIsValidWithCountryISO() : void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryBySetAPI::METHOD_NAME;
        $_REQUEST[AppCountryAPIInterface::KEY_COUNTRY_ISO] = $country->getISO();

        $method = new TestGetCountryBySetAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $this->assertSame($country, $method->getCountry());
    }

    public function test_methodIsValidIfNoCountryParams() : void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryAPI::METHOD_NAME;

        $method = new TestGetCountryAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method, 'Must be a valid response because none of the parameters are mandatory.');

        $this->assertNull($method->getCountry());
    }

    public function test_methodIsValidWithCountryID() : void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryAPI::METHOD_NAME;
        $_REQUEST[AppCountryAPIInterface::KEY_COUNTRY_ID] = $country->getID();

        $method = new TestGetCountryAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $this->assertSame($country, $method->getCountry());
    }

    public function test_methodIsValidWithCountryISO() : void
    {
        $country = $this->createTestCountry(CountryDE::ISO_CODE);

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestGetCountryAPI::METHOD_NAME;
        $_REQUEST[AppCountryAPIInterface::KEY_COUNTRY_ISO] = $country->getISO();

        $method = new TestGetCountryAPI(APIManager::getInstance());

        $this->assertSuccessfulResponse($method);

        $this->assertSame($country, $method->getCountry());
    }
}
