<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

class TestErrorResponseMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestErrorResponse';
    public const int ERROR_CODE_ERROR_RESPONSE = 184501;
    public const string ERROR_MESSAGE = 'This is a test error response.';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test method that always returns an error response.';
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return array('1.0');
    }

    public function getCurrentVersion(): string
    {
        return '1.0';
    }

    protected function init(): void
    {
    }

    protected function collectRequestData(string $version): void
    {
        $this->errorResponse(self::ERROR_CODE_ERROR_RESPONSE)
            ->setErrorMessage('This is a test error response.')
            ->send();
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }
}
