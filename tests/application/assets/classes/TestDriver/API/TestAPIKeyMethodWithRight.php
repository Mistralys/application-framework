<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for an API key method that requires a specific user right.
 * Used by {@see \AppFrameworkTests\API\Keys\KeyAuthorizationTest}.
 */
class TestAPIKeyMethodWithRight
    extends BaseAPIMethod
    implements
        RequestRequestInterface,
        JSONResponseInterface,
        APIKeyMethodInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use APIKeyMethodTrait;

    public const string METHOD_NAME = 'TestAPIKeyWithRight';

    /**
     * The right name required to call this method.
     * Defined here so tests can reference it without coupling to a string literal.
     */
    public const string TEST_RIGHT = 'TestAPIMethodRight';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method for verifying user-right enforcement in the authorization gate.';
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

    public function getRequiredRight(): ?string
    {
        return self::TEST_RIGHT;
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

    public function getResponseKeyDescriptions(): array
    {
        return array();
    }
}
