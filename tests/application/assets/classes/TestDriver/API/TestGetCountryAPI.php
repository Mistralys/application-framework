<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\API\TestGetCountryBySetAPI;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\AppCountryAPITrait;
use Application_Countries_Country;
use AppUtils\ArrayDataCollection;

/**
 * Country API method that allows selecting a country either
 * by ID or ISO code. Neither parameter is mandatory.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see CountryAPITests
 * @see TestGetCountryBySetAPI
 */
class TestGetCountryAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface, AppCountryAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use AppCountryAPITrait;

    public const string METHOD_NAME = 'TestGetCountry';

    public const array VERSIONS = array(
        self::VERSION_1_0
    );

    public const string VERSION_1_0 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1_0;

    const string KEY_COUNTRY_ID = 'countryID';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'This is a test API method to get country information.';
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return self::VERSIONS;
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    protected function init(): void
    {
        $this->registerAppCountryID();
        $this->registerAppCountryISO();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(self::KEY_COUNTRY_ID, $this->getCountry()?->getID());
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_COUNTRY_ID => 42
        );
    }

    public function getCountry() : ?Application_Countries_Country
    {
        return $this->getAppCountryIDParam()?->getCountry() ?? $this->getAppCountryISOParam()?->getCountry();
    }
}
