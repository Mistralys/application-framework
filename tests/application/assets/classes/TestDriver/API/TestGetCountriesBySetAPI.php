<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use Application\Countries\API\AppCountriesAPIInterface;
use Application\Countries\API\AppCountriesAPITrait;
use Application\Countries\API\ParamSets\AppCountriesParamRule;
use Application_Countries_Country;
use AppUtils\ArrayDataCollection;

/**
 * Test API method that identifies multiple countries by a set of possible
 * parameters using {@see AppCountriesParamRule} (OrRule mutual exclusivity).
 *
 * Callers must provide either `countryIDs` **or** `countryISOs`, not both.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see AppCountriesAPITraitTest
 * @see TestGetCountriesAPI
 */
class TestGetCountriesBySetAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    AppCountriesAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use AppCountriesAPITrait;

    public const string METHOD_NAME = 'TestGetCountriesBySet';
    public const string VERSION_1_0 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1_0;
    public const array VERSIONS = array(
        self::VERSION_1_0
    );

    const string KEY_COUNTRY_IDS = 'countryIDs';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test API method for resolving multiple countries via an OrRule param set (mutual exclusivity).';
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

    public function getGroup(): APIGroupInterface
    {
        return TestDriverAPIGroup::create();
    }

    protected function init(): void
    {
        $this->manageAppCountriesParams()->manageAllParamsRule()->register();
    }

    /**
     * Returns the resolved list of countries.
     *
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        return $this->manageAppCountriesParams()->manageAllParamsRule()->resolveValue();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $countries = $this->manageAppCountriesParams()->requireValue();

        $response->setKey(
            self::KEY_COUNTRY_IDS,
            array_map(static fn($c) => $c->getID(), $countries)
        );
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_COUNTRY_IDS => array(42, 99)
        );
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }
}
