<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;
use RuntimeException;

/**
 * Test stub whose {@see self::getActiveVersion()} override raises a
 * deterministic throwable on **every** call, unlike
 * {@see TestUnexpectedThrowableMethod} which throws once then delegates to
 * the parent implementation.
 *
 * Used to prove the hardening seam identified in the
 * 2026-09-28-api-mailings-query-and-key-usage-fixes plan is closed: the
 * framework's own response serialization (e.g.
 * {@see \Application\API\Response\JSONInfoSerializer::toArray()} and
 * {@see \Application\API\ErrorResponse::getErrorData()}) calls
 * `getActiveVersion()` again while building the error response itself, so
 * an override that never stops throwing must not be able to escape
 * {@see BaseAPIMethod}'s shared execution boundary a second time.
 *
 * @see \AppFrameworkTests\API\ProcessReturnTest
 */
class TestUnexpectedThrowableAlwaysMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestUnexpectedThrowableAlways';
    public const string EXCEPTION_MESSAGE = 'Deliberate unconditional failure for testing.';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method that raises a deterministic unexpected throwable on every getActiveVersion() call.';
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

    public function getActiveVersion(): string
    {
        throw new RuntimeException(self::EXCEPTION_MESSAGE);
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
