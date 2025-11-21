<?php

declare(strict_types=1);

namespace TestDriver\ExternalSources\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;
use TestDriver\API\TestAPIGroup;

class ExternalAPIMethod extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'ExternalLoadedAPI';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'API Method loaded from an external source folder.';
    }

    public function getGroup(): APIGroupInterface
    {
        return new TestAPIGroup();
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return array('1.0.0');
    }

    public function getCurrentVersion(): string
    {
        return '1.0.0';
    }

    protected function init(): void
    {
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

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }
}
