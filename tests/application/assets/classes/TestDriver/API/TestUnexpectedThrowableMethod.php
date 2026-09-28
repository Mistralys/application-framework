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
 * deterministic, non-{@see \Application\API\APIResponseDataException}
 * throwable — a failure point strictly **before** either data collector
 * runs, uncovered by the `183001`/`183002` collector-scoped catches
 * inside `BaseAPIMethod::_process()`.
 *
 * Used to prove that {@see BaseAPIMethod}'s shared execution boundary
 * routes such a pre-collector failure through
 * {@see \Application\API\APIMethodInterface::ERROR_UNEXPECTED_THROWABLE}
 * on both {@see BaseAPIMethod::process()} (HTTP 500 JSON, never an HTML
 * page) and {@see BaseAPIMethod::processReturn()} (the corresponding
 * {@see \Application\API\ErrorResponsePayload}).
 *
 * @see \AppFrameworkTests\API\ProcessReturnTest
 * @see \AppFrameworkTests\API\ThrowableLoggingTest
 * @see \AppFrameworkTests\API\UnexpectedThrowableLiveHTTPTest
 */
class TestUnexpectedThrowableMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestUnexpectedThrowable';
    public const string EXCEPTION_MESSAGE = 'Deliberate pre-collector failure for testing.';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method that raises a deterministic unexpected throwable before either data collector runs.';
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

    private bool $versionCallThrown = false;

    /**
     * Deliberately raises a throwable before either data collector runs,
     * simulating a pre-collector failure class (e.g. `updateLastUsed()` or
     * a real `getActiveVersion()` override raising an unrelated exception).
     *
     * Throws on the first call only: the framework's own response
     * serialization (e.g. {@see \Application\API\Response\JSONInfoSerializer::toArray()})
     * calls `getActiveVersion()` again while building the error response
     * itself — a realistic pre-collector failure happens once, during
     * initial dispatch, not on every subsequent access to this accessor.
     */
    public function getActiveVersion(): string
    {
        if(!$this->versionCallThrown) {
            $this->versionCallThrown = true;
            throw new RuntimeException(self::EXCEPTION_MESSAGE);
        }

        return parent::getActiveVersion();
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
