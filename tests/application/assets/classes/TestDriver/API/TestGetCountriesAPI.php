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
use AppUtils\ArrayDataCollection;

/**
 * Test API method that registers country IDs and ISO parameters
 * individually (without an OrRule), allowing isolated testing of
 * each parameter path.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see AppCountriesAPITraitTest
 * @see TestGetCountriesBySetAPI
 */
class TestGetCountriesAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    AppCountriesAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use AppCountriesAPITrait;

    public const string METHOD_NAME = 'TestGetCountries';
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
        return 'Test API method for resolving multiple countries individually (no OrRule).';
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
        $this->manageAppCountriesParams()->manageIDs()->register();
        $this->manageAppCountriesParams()->manageISOs()->register();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $countries = $this->manageAppCountriesParams()->resolveValue();

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
