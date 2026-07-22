<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestAPIKeyMethodWithRight;
use TestDriver\API\TestVersionedMethod;

/**
 * Verifies the two-check authorization gate introduced in BaseAPIMethod::authorize():
 * (1) method-access whitelist and (2) pseudo-user right enforcement.
 *
 * Test cases:
 *  1. method-access denied          → HTTP 403 / error 183005
 *  2. method-access granted (individual grant) → success
 *  3. method-access granted (grantAll)         → success
 *  4. user-rights denied            → HTTP 403 / error 183006
 *  5. user-rights granted           → success
 *  6. null-right skip               → user-right check skipped, success
 *  7. non-key method skip           → authorize() is a no-op, success
 *  8. updateLastUsed after success  → usage count increments by 1
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

    // endregion

    // region: _Tests

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
     */
    public function test_methodAccessGrantedIndividual(): void
    {
        $key = $this->createTestAPIKeyWithRights(
            TestAPIKeyMethodWithRight::METHOD_NAME,
            array(TestAPIKeyMethodWithRight::TEST_RIGHT)
        );

        $method = $this->createMethodWithRight($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * A key with grantAll() passes the access check for any method.
     */
    public function test_methodAccessGrantedAll(): void
    {
        $key = $this->createTestAPIKey();
        $key->getMethods()->grantAll();
        $key->getPseudoUser()->setRights(array(TestAPIKeyMethodWithRight::TEST_RIGHT));

        $method = $this->createMethodWithRight($key);

        $this->assertSuccessfulResponse($method->processReturn());
    }

    /**
     * A key with method access but a pseudo-user that lacks the required right
     * receives HTTP 403 / 183006.
     */
    public function test_userRightsDenied(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);
        // Pseudo-user has NO rights (default for a fresh test user).

        $method = $this->createMethodWithRight($key);

        $this->assertErrorResponseCode(
            $method->processReturn(),
            APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS
        );
    }

    /**
     * A key with method access and a pseudo-user that holds the required right
     * passes authorization.
     */
    public function test_userRightsGranted(): void
    {
        $key = $this->createTestAPIKeyWithRights(
            TestAPIKeyMethodWithRight::METHOD_NAME,
            array(TestAPIKeyMethodWithRight::TEST_RIGHT)
        );

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
     * incrementing the API key's usage count by exactly 1.
     */
    public function test_updateLastUsedAfterAuthorization(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);
        $key->getPseudoUser()->setRights(array(TestAPIKeyMethodWithRight::TEST_RIGHT));

        $usageCountBefore = $key->getUsageCount();

        $method = $this->createMethodWithRight($key);
        $this->assertSuccessfulResponse($method->processReturn());

        $this->assertSame(
            $usageCountBefore + 1,
            $key->getUsageCount(),
            'updateLastUsed() must increment the usage count by 1 after successful authorization.'
        );
    }

    // endregion
}
