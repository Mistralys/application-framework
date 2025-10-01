<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\AppCountryAPITrait;
use AppUtils\ArrayDataCollection;

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
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }
}
