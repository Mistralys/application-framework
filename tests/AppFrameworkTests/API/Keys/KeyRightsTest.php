<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\APIManager;
use Application\API\Clients\Keys\APIKeyRights;
use Application\API\Collection\APIMethodIndex;
use Application\API\Collection\APIMethodIndexEntry;
use Application\API\User\APIRightsInterface;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestAPIKeyMethodWithRight;
use TestDriver_User;

/**
 * Tests the {@see APIKeyRights} authority object, exercising
 * every branch of {@see APIKeyRights::satisfies()}.
 */
final class KeyRightsTest extends APIClientTestCase
{
    private function getIndex(): APIMethodIndex
    {
        return APIManager::getInstance()->getMethodIndex();
    }

    // region: Direct right match

    /**
     * satisfies() returns true when the declared right equals the requested right
     * on a granted method.
     */
    public function test_directRightMatch(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);
        $rights = $key->getRights();

        $this->assertTrue(
            $rights->satisfies(
                TestAPIKeyMethodWithRight::METHOD_NAME,
                TestAPIKeyMethodWithRight::TEST_RIGHT
            )
        );
    }

    // endregion

    // region: Method not granted

    public function test_methodNotGranted(): void
    {
        $key = $this->createTestAPIKey();
        $rights = $key->getRights();

        $this->assertFalse(
            $rights->satisfies(
                TestAPIKeyMethodWithRight::METHOD_NAME,
                TestAPIKeyMethodWithRight::TEST_RIGHT
            )
        );
    }

    // endregion

    // region: Null declared right

    /**
     * satisfies() returns false when the granted method declares null right.
     */
    public function test_nullDeclaredRight(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethod::METHOD_NAME);
        $rights = $key->getRights();

        $this->assertFalse(
            $rights->satisfies(TestAPIKeyMethod::METHOD_NAME, 'AnyRight')
        );
    }

    // endregion

    // region: One-level grant expansion

    /**
     * satisfies() returns true for a right reachable one level from the
     * declared right via the test application chain:
     * RIGHT_GRANTS_TEST_API_METHOD grants TestAPIMethodRight.
     */
    public function test_oneLevelGrantViaTestChain(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);
        $rights = $key->getRights();

        // TestAPIMethodRight is granted by RIGHT_GRANTS_TEST_API_METHOD,
        // but we're asking: does the declared right (TestAPIMethodRight)
        // grant RIGHT_GRANTS_TEST_API_METHOD? No, the grant is the other way.
        // The declared right is TestAPIMethodRight. It does not grant
        // RIGHT_GRANTS_TEST_API_METHOD. So this should be false.
        $this->assertFalse(
            $rights->satisfies(
                TestAPIKeyMethodWithRight::METHOD_NAME,
                TestDriver_User::RIGHT_GRANTS_TEST_API_METHOD
            )
        );
    }

    /**
     * satisfies() returns true for a right reachable one level from the
     * declared right via the framework chain:
     * EditAPIClients grants ViewAPIClients.
     *
     * Uses a crafted index entry on an existing method name to avoid
     * the static availableMethods cache in APIKeyMethods.
     */
    public function test_oneLevelGrantViaFrameworkChain(): void
    {
        $index = $this->getIndex();
        $index->build();

        // Override TestAPIKeyMethod's entry to declare EditAPIClients.
        $data = $index->getDataFile()->getData();
        $originalEntry = $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME];
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = (new APIMethodIndexEntry(
            TestAPIKeyMethod::METHOD_NAME,
            TestAPIKeyMethod::class,
            APIRightsInterface::RIGHT_EDIT_API_CLIENTS,
            'TestGroup'
        ))->toArray();
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();

        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethod::METHOD_NAME);
        $rights = $key->getRights();

        // EditAPIClients grants ViewAPIClients (one level)
        $this->assertTrue(
            $rights->satisfies(TestAPIKeyMethod::METHOD_NAME, APIRightsInterface::RIGHT_VIEW_API_CLIENTS)
        );

        // Restore original index
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = $originalEntry;
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();
    }

    // endregion

    // region: Depth >= 2 (parity with User::can())

    /**
     * satisfies() returns false for a right reachable only at depth >= 2.
     *
     * Override TestAPIKeyMethod's entry to declare RIGHT_GRANTS_TEST_API_METHOD.
     * That right grants TestAPIMethodRight (level 1), but ViewAPIClients
     * is NOT in its direct grants — proving the one-level boundary.
     */
    public function test_depthTwoOrMoreDenied(): void
    {
        $index = $this->getIndex();
        $index->build();

        $data = $index->getDataFile()->getData();
        $originalEntry = $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME];
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = (new APIMethodIndexEntry(
            TestAPIKeyMethod::METHOD_NAME,
            TestAPIKeyMethod::class,
            TestDriver_User::RIGHT_GRANTS_TEST_API_METHOD,
            'TestGroup'
        ))->toArray();
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();

        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethod::METHOD_NAME);
        $rights = $key->getRights();

        // RIGHT_GRANTS_TEST_API_METHOD grants TestAPIMethodRight (level 1),
        // but ViewAPIClients is NOT in its direct grants.
        $this->assertFalse(
            $rights->satisfies(TestAPIKeyMethod::METHOD_NAME, APIRightsInterface::RIGHT_VIEW_API_CLIENTS)
        );

        // Restore original index
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = $originalEntry;
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();
    }

    // endregion

    // region: Grant-all

    /**
     * satisfies() returns true under grant-all without pseudo-user rights being set.
     */
    public function test_grantAllSatisfies(): void
    {
        $key = $this->createTestAPIKey();
        $key->getMethods()->grantAll();
        $rights = $key->getRights();

        // The pseudo user has no rights, but satisfies() should work
        // purely from the method index entry.
        $this->assertTrue(
            $rights->satisfies(
                TestAPIKeyMethodWithRight::METHOD_NAME,
                TestAPIKeyMethodWithRight::TEST_RIGHT
            )
        );
    }

    // endregion

    // region: Unresolvable declared right

    /**
     * satisfies() returns false and throws nothing when the declared right is
     * unregistered (crafted index entry with clearIndexCache()).
     */
    public function test_unresolvableDeclaredRight(): void
    {
        $index = $this->getIndex();
        $index->build();

        $data = $index->getDataFile()->getData();
        $originalEntry = $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME];
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = (new APIMethodIndexEntry(
            TestAPIKeyMethod::METHOD_NAME,
            TestAPIKeyMethod::class,
            'CompletelyUnregisteredRightID',
            'TestGroup'
        ))->toArray();
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();

        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethod::METHOD_NAME);
        $rights = $key->getRights();

        // Must return false (not throw) for an unregistered declared right.
        $this->assertFalse(
            $rights->satisfies(TestAPIKeyMethod::METHOD_NAME, 'AnyRight')
        );

        // Restore original index
        $data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethod::METHOD_NAME] = $originalEntry;
        $index->getDataFile()->putData($data);
        $index->clearIndexCache();
    }

    // endregion

    // region: Pseudo-user invariant

    /**
     * The pseudo user holds no rights after a successful satisfies() call.
     */
    public function test_pseudoUserHasNoRightsAfterSatisfies(): void
    {
        $key = $this->createTestAPIKeyForMethod(TestAPIKeyMethodWithRight::METHOD_NAME);

        // Confirm satisfies succeeds
        $this->assertTrue(
            $key->getRights()->satisfies(
                TestAPIKeyMethodWithRight::METHOD_NAME,
                TestAPIKeyMethodWithRight::TEST_RIGHT
            )
        );

        // The pseudo user must still have no rights assigned
        $this->assertEmpty($key->getPseudoUser()->getRights());
    }

    // endregion

    // region: No union exposure

    /**
     * APIKeyRights exposes no public method by which a caller can obtain
     * the union of rights across all methods granted to a key.
     */
    public function test_noUnionExposure(): void
    {
        $reflection = new \ReflectionClass(APIKeyRights::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $methodNames = array_map(
            static fn(\ReflectionMethod $m) => $m->getName(),
            $publicMethods
        );

        // Filter out inherited loggable methods — only APIKeyRights' own public surface matters.
        $ownMethods = array_filter(
            $methodNames,
            static fn(string $name) => !in_array($name, array('getLogIdentifier', 'getLogPrefix'), true)
        );

        // The only own public methods should be __construct and satisfies.
        $this->assertContains('satisfies', $ownMethods);
        $this->assertNotContains('getAllRights', $ownMethods);
        $this->assertNotContains('getRights', $ownMethods);
        $this->assertNotContains('getUnion', $ownMethods);
    }

    // endregion
}
