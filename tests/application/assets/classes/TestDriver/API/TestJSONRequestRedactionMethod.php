<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub combining a JSON request body with a method that always
 * returns an error response, so that
 * {@see \Application\API\Traits\JSONRequestTrait::collectRequestErrorData()}
 * is exercised through {@see BaseAPIMethod::errorResponse()} — proving
 * the parsed request body it echoes is present in a development
 * environment and absent in production, per
 * {@see \Application\API\ErrorResponse::getErrorData()}'s
 * `Application::isDevelEnvironment()` gate.
 *
 * @see \AppFrameworkTests\API\ErrorResponseTest
 * @see \AppFrameworkTests\API\ErrorPayloadRedactionLiveHTTPTest
 */
class TestJSONRequestRedactionMethod
    extends BaseAPIMethod
    implements
    JSONRequestInterface,
    JSONResponseInterface
{
    use JSONRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestJSONRequestRedaction';
    public const int ERROR_CODE = 184601;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method that always fails, used to verify JSON request body redaction in error responses.';
    }

    public function getGroup(): APIGroupInterface
    {
        return TestDriverAPIGroup::create();
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
        return array('1.0');
    }

    public function getCurrentVersion(): string
    {
        return '1.0';
    }

    protected function init(): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->errorResponse(self::ERROR_CODE)
            ->setErrorMessage('This method always fails, to exercise request-data redaction.')
            ->send();
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }

    public function getResponseKeyDescriptions(): array
    {
        return array();
    }
}
