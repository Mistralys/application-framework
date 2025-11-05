<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace application\assets\classes\TestDriver\API;

use AppFrameworkTests\Countries\CountryAPITests;
use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\AppCountryAPITrait;
use Application\Countries\API\ParamSets\AppCountryParamRule;
use Application_Countries_Country;
use AppUtils\ArrayDataCollection;

/**
 * Test API method that identifies a country by a set of
 * possible parameters using {@see AppCountryParamRule}.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see CountryAPITests Matching test case
 * @see TestGetCountryAPI
 */
class TestGetCountryBySetAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    AppCountryAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use AppCountryAPITrait;

    public const string METHOD_NAME = 'TestGetCountryBySet';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(
        self::VERSION_1
    );
    const string KEY_COUNTRY_ID = 'countryID';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
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

    public function getDescription(): string
    {
        return 'Stub Country Param Set API for testing purposes.';
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    protected function init(): void
    {
        $this->registerAppCountryParams();
    }

    public function getCountry() : ?Application_Countries_Country
    {
        return $this->getAppCountryParamRule()?->getCountry();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $country = $this->requireAppCountry();

        $response->setKey(self::KEY_COUNTRY_ID, $country->getID());
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_COUNTRY_ID => 42
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