<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Collection\APIMethodIndex;
use Application\API\Collection\APIMethodIndexEntry;
use Application\AppFactory;
use Application\Disposables\DisposableDisposedException;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;
use TestDriver\API\TestAPIKeyDryRunTransactionMethod;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestAPIKeyMethodWithRight;
use TestDriver\API\TestVersionedMethod;

/**
 * Verifies the authorization gate implemented in BaseAPIMethod::authorize():
 * (1) API key resolution, (2) method-access whitelist, and (3) APIKeyRights
 * satisfaction. Authority derives from the key's method grants via
 * {@see \Application\API\Clients\Keys\APIKeyRights::satisfies()} — not
 * from pseudo-user rights.
 *
 * Test cases:
 *  1. unknown/invalid API key value → HTTP 401 / error 183007
 *  2. method-access denied          → HTTP 403 / error 183005
 *  3. method-access granted (individual grant) → success
 *  4. method-access granted (grantAll)         → success
 *  5. insufficient-rights denied (unresolvable right) → HTTP 403 / error 183006
 *  6. method-grant derived rights   → success (no pseudo-user rights needed)
 *  7. null-right skip               → user-right check skipped, success
 *  8. non-key method skip           → authorize() is a no-op, success
 *  9. updateLastUsed after success  → usage count increments by 1
 * 10. pseudo-user rights alone      → do NOT authorize a method
 *
 * Also verifies {@see APIKeyRecord::updateLastUsed()}'s transactional
 * behavior directly:
 * 11. standalone call (no transaction open) → commits its own transaction
 * 12. call within an already-open transaction → writes through it, no commit/rollback
 * 13. a failing write → rolls back only its own transaction and rethrows
 * 14. a dryRun=1 call via {@see TestAPIKeyDryRunTransactionMethod} → the audit
 *     write survives the method's own, later transaction rollback
 */
final class KeyAuthorizationTest extends APIClientTestCase
{
    use APIMethodTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $_REQUEST = array();
    }

    // region: Helpers

    /**
     * Creates a TestAPIKeyMethodWithRight instance with the given key already injected
     * and the method request parameter pre-set.
     */
    private function createMethodWithRight(APIKeyRecord $key): TestAPIKeyMethodWithRight
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestAPIKeyMethodWithRight::METHOD_NAME;

        $method = new TestAPIKeyMethodWithRight(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);

        return $method;
    }

    /**
     * Deletes a test API key and its owning client, requiring an
     * already-open transaction (the collection's deleteRecord() enforces
     * this via {@see DBHelper::requireTransaction()}).
     */
    private function deleteTestAPIKeyAndClient(APIKeyRecord $key): void
    {
        $clientID = $key->getClientID();
        $client = AppFactory::createAPIClients()->getByID($clientID);

        $key->getCollection()->deleteRecord($key);
        AppFactory::createAPIClients()->deleteRecord($client);
    }

    // endregion

    // region: _Tests

    /**
     * A value submitted for the API key parameter that does not match any
     * known key receives HTTP 401 / 183007. This is distinct from a wholly
     * missing key, which never reaches authorize() at all — it is rejected
     * earlier by the generic required-parameter validation (183003).
     */
    public function test_unknownAPIKeyReturnsInvalidKeyError(): void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestAPIKeyMethodWithRight::METHOD_NAME;

        $method = new TestAPIKeyMethodWithRight(APIManager::getInstance());
        $method->manageParamAPIKey()->getParam()?->selectValue('does-not-match-any-known-key');

        $this->assertErrorResponseCode(
            $method->processReturn(),
            APIMethodInterface::ERROR_API_KEY_INVALID
        );
    }

    /**
     * A key that has NOT been granted the method receives HTTP 403 / 183005.
     */
    public function test_methodAccessDenied(): void
    {
        $key = $this->createTestAPIKey();
        // Method intentionally not granted to the key.

        $method = $this->createMethodWithRight($key);

        $this->assertErrorResponseCode(
            $method->processReturn(),
            APIMethodInterface::ERROR_METHOD_NOT_GRANTED
        );
    }

    /**
     * A key with an individual method grant passes the access check.
     * Authority derives from the method grant — no pseudo-user rights needed.
     */
    public function test_methodAccessGrantedIndividual(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);

        $method = $this->createMethodWithRight($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * A key with grantAll() passes the access check for any method.
     * Authority derives from the grant-all flag — no pseudo-user rights needed.
     */
    public function test_methodAccessGrantedAll(): void
    {
        $key = $this->createTestAPIKey();
        $key->getMethods()->grantAll();

        $method = $this->createMethodWithRight($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * A granted method whose index entry declares an unregistered right
     * yields HTTP 403 / 183006 because satisfies() cannot resolve the
     * declared right and fails closed.
     */
    public function test_unresolvableDeclaredRightDenied(): void
    {
        $index = APIManager::getInstance()->getMethodIndex();
        $index->build();

        $data = $index->getDataFile()->getData();
        $originalEntry = $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethodWithRight::METHOD_NAME];

        // Craft an entry with an unregistered right that no rights group knows about.
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethodWithRight::METHOD_NAME] = (new APIMethodIndexEntry(
            TestAPIKeyMethodWithRight::METHOD_NAME,
            TestAPIKeyMethodWithRight::class,
            'NonExistentRight_Unresolvable',
            'TestGroup'
        ))->toArray();
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();

        try {
            $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);

            $method = $this->createMethodWithRight($key);

            $this->assertErrorResponseCode(
                $method->processReturn(),
                APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS
            );
        } finally {
            $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethodWithRight::METHOD_NAME] = $originalEntry;
            $index->getDataFile()->putData($data);
            $index->clearIndexCache();
        }
    }

    /**
     * A key with method access authorizes successfully — authority derives
     * from the method grant via APIKeyRights::satisfies(), not pseudo-user rights.
     */
    public function test_methodGrantDerivedRights(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);

        $method = $this->createMethodWithRight($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * When getRequiredRight() returns null, the user-right check is skipped;
     * only the method-access check applies.
     */
    public function test_nullRightSkipsUserCheck(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethod::METHOD_NAME);
        // Pseudo-user has no rights, but TestAPIKeyMethod::getRequiredRight() returns null.

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestAPIKeyMethod::METHOD_NAME;

        $method = new TestAPIKeyMethod(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * Methods that do not implement APIKeyMethodInterface pass through authorize()
     * without any effect.
     */
    public function test_nonKeyMethodSkipsAuthorize(): void
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestVersionedMethod::METHOD_NAME;

        $method = new TestVersionedMethod(APIManager::getInstance());

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * After a successful authorization, updateLastUsed() must have been called,
     * incrementing the API key's usage count by exactly 1. Authority derives
     * from method grants alone — no pseudo-user rights needed.
     */
    public function test_updateLastUsedAfterAuthorization(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);

        $usageCountBefore = $key->getUsageCount();

        $method = $this->createMethodWithRight($key);
        $this->assertSuccessfulResponse($method->processReturn());

        $this->assertSame(
            $usageCountBefore + 1,
            $key->getUsageCount(),
            'updateLastUsed() must increment the usage count by 1 after successful authorization.'
        );
    }

    /**
     * Setting rights on the pseudo user does NOT authorize a method
     * the key was not granted. Authority derives from method grants,
     * not pseudo-user rights.
     */
    public function test_pseudoUserRightsAloneDoNotAuthorize(): void
    {
        $key = $this->createTestAPIKey();
        // Grant the right on the pseudo user but do NOT grant the method.
        $key->getPseudoUser()->setRights(array(TestAPIKeyMethodWithRight::TEST_RIGHT));

        $method = $this->createMethodWithRight($key);

        $this->assertErrorResponseCode(
            $method->processReturn(),
            APIMethodInterface::ERROR_METHOD_NOT_GRANTED
        );
    }

    /**
     * Called standalone with no transaction open (the production scenario —
     * BaseAPIMethod never opens an ambient transaction), updateLastUsed()
     * must start, own, and commit its own transaction, persisting the change.
     */
    public function test_updateLastUsedStandaloneCommitsOwnTransaction(): void
    {
        $key = $this->createTestAPIKey();
        $usageCountBefore = $key->getUsageCount();

        // Commit the ambient fixture transaction (started in setUp()) so
        // that updateLastUsed() sees no transaction open, matching production.
        DBHelper::commitTransaction();

        try {
            $key->updateLastUsed();

            $this->assertFalse(
                DBHelper::isTransactionStarted(),
                'updateLastUsed() must leave no transaction open after a standalone call.'
            );

            $key->refreshData();

            $this->assertSame(
                $usageCountBefore + 1,
                $key->getUsageCount(),
                'updateLastUsed() must persist the incremented usage count when called standalone.'
            );
        } finally {
            DBHelper::startTransaction();
            $this->deleteTestAPIKeyAndClient($key);
            DBHelper::commitTransaction();
        }
    }

    /**
     * Called inside an already-open transaction, updateLastUsed() must
     * write through it without committing or rolling back — the caller's
     * transaction remains entirely authoritative and stays open afterward.
     */
    public function test_updateLastUsedWithinOpenTransactionDoesNotCommitOrRollback(): void
    {
        // setUp() already started the ambient transaction this test runs in.
        $key = $this->createTestAPIKey();
        $usageCountBefore = $key->getUsageCount();

        $key->updateLastUsed();

        $this->assertTrue(
            DBHelper::isTransactionStarted(),
            'updateLastUsed() must not commit or roll back a transaction it did not open.'
        );
        $this->assertSame(
            $usageCountBefore + 1,
            $key->getUsageCount(),
            'updateLastUsed() must still apply the usage count increment within the caller-owned transaction.'
        );

        // tearDown() rolls back the still-open ambient transaction, cleaning up.
    }

    /**
     * When its own write fails, updateLastUsed() must roll back only the
     * transaction it opened and rethrow the original failure, leaving no
     * transaction open behind it.
     */
    public function test_updateLastUsedRollsBackOwnTransactionOnFailure(): void
    {
        $key = $this->createTestAPIKey();

        // Capture identifiers before disposal clears the record's data.
        $clientID = $key->getClientID();
        $keyID = $key->getID();

        // Commit the ambient transaction so updateLastUsed() opens its own.
        DBHelper::commitTransaction();

        // Disposing the record makes the subsequent save() fail via
        // requireNotDisposed(), without needing to break the DB connection.
        $key->dispose();

        try {
            $this->expectException(DisposableDisposedException::class);
            $key->updateLastUsed();
        } finally {
            $this->assertFalse(
                DBHelper::isTransactionStarted(),
                'updateLastUsed() must leave no transaction open after rolling back its own failed write.'
            );

            // The key was never persisted with the failed update; the
            // ambient transaction that held its creation was already
            // committed above, so it is removed explicitly here.
            DBHelper::startTransaction();
            $client = AppFactory::createAPIClients()->getByID($clientID);
            $freshKey = $client->createAPIKeys()->getByID($keyID);
            $client->createAPIKeys()->deleteRecord($freshKey);
            AppFactory::createAPIClients()->deleteRecord($client);
            DBHelper::commitTransaction();
        }
    }

    /**
     * A dryRun=1 call through a method combining API-key authentication
     * with a method-owned transaction rollback: updateLastUsed() (called
     * by BaseAPIMethod::_process() before the method's own body runs) has
     * already committed its own transaction by the time the method's
     * collectResponseData() opens and rolls back its own — so the usage
     * count and last-used date increments survive that later rollback.
     */
    public function test_dryRunCallSurvivesMethodOwnedRollback(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyDryRunTransactionMethod::METHOD_NAME);
        $usageCountBefore = $key->getUsageCount();
        $lastUsedBefore = $key->getLastUsed();

        // Commit the ambient transaction so updateLastUsed() sees no
        // transaction open, matching the real BaseAPIMethod::_process() flow.
        DBHelper::commitTransaction();

        try {
            $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestAPIKeyDryRunTransactionMethod::METHOD_NAME;

            $method = new TestAPIKeyDryRunTransactionMethod(APIManager::getInstance());
            $method->manageParamAPIKey()->selectKey($key);
            $method->selectDryRun(true);

            $this->assertSuccessfulResponse($method->processReturn());

            $this->assertFalse(
                DBHelper::isTransactionStarted(),
                'No transaction must remain open after the dry-run call completes.'
            );

            $key->refreshData();

            $this->assertSame(
                $usageCountBefore + 1,
                $key->getUsageCount(),
                'The usage count increment must survive the method-owned dry-run rollback.'
            );
            $this->assertNotEquals(
                $lastUsedBefore,
                $key->getLastUsed(),
                'The last-used date update must survive the method-owned dry-run rollback.'
            );
        } finally {
            DBHelper::startTransaction();
            $this->deleteTestAPIKeyAndClient($key);
            DBHelper::commitTransaction();
        }
    }

    // endregion
}
