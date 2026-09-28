<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;
use DBHelper;

/**
 * Test stub combining an API-key-authenticated method with a dry-run
 * capable body that opens and conditionally rolls back its own,
 * method-owned transaction — mirroring the production pattern in
 * `FinalizeMailingAPI::collectResponseData()` (hcp-editor).
 *
 * Used to prove that {@see \Application\API\Clients\Keys\APIKeyRecord::updateLastUsed()},
 * called by {@see BaseAPIMethod::_process()} before this method's body
 * runs, persists the API key's usage audit fact independently of this
 * method's own `dryRun` rollback: `_process()` calls `updateLastUsed()`
 * first (which starts and commits its own transaction, since none is
 * open at that point), and only afterward does `collectResponseData()`
 * open and roll back a transaction of its own.
 *
 * @see \AppFrameworkTests\API\Keys\KeyAuthorizationTest
 */
class TestAPIKeyDryRunTransactionMethod
    extends BaseAPIMethod
    implements
        RequestRequestInterface,
        JSONResponseInterface,
        APIKeyMethodInterface,
        DryRunAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use APIKeyMethodTrait;
    use DryRunAPITrait;

    public const string METHOD_NAME = 'TestAPIKeyDryRunTransaction';

    public function getRequiredRight() : ?string
    {
        return null;
    }

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method combining API-key authentication with a dry-run capable, method-owned transaction.';
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
        $this->registerDryRunParam();
    }

    protected function collectRequestData(string $version): void
    {
    }

    /**
     * Opens a method-owned transaction — mirroring the production
     * dry-run pattern — and rolls it back when `dryRun=true`, commits
     * it otherwise. This runs strictly after `updateLastUsed()` has
     * already been called (and, since no transaction was open at that
     * point, already committed) by {@see BaseAPIMethod::_process()}.
     */
    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        DBHelper::startTransaction();

        if ($this->isDryRun()) {
            DBHelper::rollbackTransaction();
        } else {
            DBHelper::commitTransaction();
        }

        $response->setKey(DryRunAPIInterface::PARAM_DRY_RUN, $this->isDryRun());
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
