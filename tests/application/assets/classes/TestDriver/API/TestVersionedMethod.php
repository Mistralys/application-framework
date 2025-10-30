<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\API\Versioning\APIVersionInterface;
use Application\API\Versioning\VersionedAPIInterface;
use Application\API\Versioning\VersionedAPITrait;
use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\FolderInfo;
use TestDriver\API\Versioned\Versioned_1_0;

class TestVersionedMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    VersionedAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use VersionedAPITrait;

    public const string METHOD_NAME = 'TestVersioned';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'API method for testing API methods that handle versions with separate classes.';
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public const string CURRENT_VERSION = Versioned_1_0::VERSION;

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    protected function init(): void
    {
    }

    protected function collectRequestData(string $version): void
    {
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    public function getVersionFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/TestVersioned');
    }

    protected function _collectResponseData(ArrayDataCollection $response, APIVersionInterface $version): void
    {
    }
}
