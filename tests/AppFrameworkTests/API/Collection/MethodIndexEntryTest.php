<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Collection;

use Application\API\APIException;
use Application\API\APIManager;
use Application\API\Collection\APIMethodIndex;
use Application\API\Collection\APIMethodIndexEntry;
use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver\API\TestAPIKeyDryRunTransactionMethod;
use TestDriver\API\TestAPIKeyMethod;
use TestDriver\API\TestAPIKeyMethodWithRight;
use TestDriver\API\TestJSONRequestRedactionMethod;
use TestDriver\API\TestUnexpectedThrowableMethod;

/**
 * Tests the versioned {@see APIMethodIndex} and its
 * {@see APIMethodIndexEntry} hydration.
 */
final class MethodIndexEntryTest extends ApplicationTestCase
{
    private function getIndex(): APIMethodIndex
    {
        return APIManager::getInstance()->getMethodIndex();
    }

    // region: Schema versioning

    public function test_indexContainsSchemaVersion(): void
    {
        $index = $this->getIndex();
        $index->build();

        $data = $index->getDataFile()->getData();

        $this->assertArrayHasKey(APIMethodIndex::KEY_SCHEMA_VERSION, $data);
        $this->assertSame(APIMethodIndex::SCHEMA_VERSION, $data[APIMethodIndex::KEY_SCHEMA_VERSION]);
    }

    public function test_indexContainsMethodsKey(): void
    {
        $index = $this->getIndex();
        $index->build();

        $data = $index->getDataFile()->getData();

        $this->assertArrayHasKey(APIMethodIndex::KEY_METHODS, $data);
        $this->assertIsArray($data[APIMethodIndex::KEY_METHODS]);
    }

    public function test_autoRebuildOnMissingVersion(): void
    {
        $index = $this->getIndex();
        $index->build();

        // Write a legacy (v1) flat index directly to disk
        $index->getDataFile()->putData(array('SomeMethod' => 'SomeClass'));

        $index->clearIndexCache();

        // Reading should trigger auto-rebuild, not throw
        $names = $index->getMethodNames();
        $this->assertNotEmpty($names);

        // The disk file should now have the correct schema version
        $data = $index->getDataFile()->getData();
        $this->assertSame(APIMethodIndex::SCHEMA_VERSION, $data[APIMethodIndex::KEY_SCHEMA_VERSION]);
    }

    public function test_autoRebuildOnWrongVersion(): void
    {
        $index = $this->getIndex();
        $index->build();

        // Write a document with a wrong schema version
        $data = $index->getDataFile()->getData();
        $data[APIMethodIndex::KEY_SCHEMA_VERSION] = 999;
        $index->getDataFile()->putData($data);

        $index->clearIndexCache();

        // Should auto-rebuild rather than throw
        $names = $index->getMethodNames();
        $this->assertNotEmpty($names);
    }

    // endregion

    // region: Entry accessors

    public function test_getEntryForKeyMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $entry = $index->getEntry(TestAPIKeyMethodWithRight::METHOD_NAME);

        $this->assertSame(TestAPIKeyMethodWithRight::METHOD_NAME, $entry->getMethodName());
        $this->assertSame(TestAPIKeyMethodWithRight::class, $entry->getClassName());
        $this->assertSame(TestAPIKeyMethodWithRight::TEST_RIGHT, $entry->getRequiredRight());
        $this->assertNotEmpty($entry->getGroupID());
    }

    public function test_getEntryForNonKeyMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $entry = $index->getEntry(TestAPIKeyMethod::METHOD_NAME);

        $this->assertSame(TestAPIKeyMethod::METHOD_NAME, $entry->getMethodName());
        $this->assertSame(TestAPIKeyMethod::class, $entry->getClassName());
        $this->assertNull($entry->getRequiredRight());
    }

    /**
     * The {@see TestAPIKeyDryRunTransactionMethod} fixture (API-key auth
     * combined with a dry-run capable, method-owned transaction) must be
     * mapped in the index before it can be dispatched by name.
     */
    public function test_getEntryForAPIKeyDryRunTransactionMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $entry = $index->getEntry(TestAPIKeyDryRunTransactionMethod::METHOD_NAME);

        $this->assertSame(TestAPIKeyDryRunTransactionMethod::METHOD_NAME, $entry->getMethodName());
        $this->assertSame(TestAPIKeyDryRunTransactionMethod::class, $entry->getClassName());
        $this->assertNull($entry->getRequiredRight());
    }

    /**
     * The {@see TestUnexpectedThrowableMethod} fixture (pre-collector
     * throwable via a `getActiveVersion()` override) must be mapped in the
     * index before it can be dispatched by name.
     */
    public function test_getEntryForUnexpectedThrowableMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $entry = $index->getEntry(TestUnexpectedThrowableMethod::METHOD_NAME);

        $this->assertSame(TestUnexpectedThrowableMethod::METHOD_NAME, $entry->getMethodName());
        $this->assertSame(TestUnexpectedThrowableMethod::class, $entry->getClassName());
        $this->assertNull($entry->getRequiredRight());
    }

    /**
     * The {@see TestJSONRequestRedactionMethod} fixture (JSON request body
     * echoed via `collectRequestErrorData()` on an always-failing method)
     * must be mapped in the index before it can be dispatched by name.
     */
    public function test_getEntryForJSONRequestRedactionMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $entry = $index->getEntry(TestJSONRequestRedactionMethod::METHOD_NAME);

        $this->assertSame(TestJSONRequestRedactionMethod::METHOD_NAME, $entry->getMethodName());
        $this->assertSame(TestJSONRequestRedactionMethod::class, $entry->getClassName());
        $this->assertNull($entry->getRequiredRight());
    }

    public function test_getEntryThrowsForUnknownMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $this->expectException(APIException::class);
        $this->expectExceptionCode(APIException::ERROR_METHOD_NOT_IN_INDEX);

        $index->getEntry('NonExistentMethod');
    }

    // endregion

    // region: Backward-compatible getMethodClass()

    public function test_getMethodClassReturnsClassName(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $this->assertSame(
            TestAPIKeyMethodWithRight::class,
            $index->getMethodClass(TestAPIKeyMethodWithRight::METHOD_NAME)
        );
    }

    public function test_getMethodClassThrowsForUnknownMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $this->expectException(APIException::class);
        $this->expectExceptionCode(APIException::ERROR_METHOD_NOT_IN_INDEX);

        $index->getMethodClass('NonExistentMethod');
    }

    // endregion

    // region: methodExists() and getMethodNames()

    public function test_methodExistsReturnsTrueForKnownMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $this->assertTrue($index->methodExists(TestAPIKeyMethodWithRight::METHOD_NAME));
    }

    public function test_methodExistsReturnsFalseForUnknownMethod(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $this->assertFalse($index->methodExists('NonExistentMethod'));
    }

    public function test_getMethodNamesContainsKnownMethods(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        $names = $index->getMethodNames();
        $this->assertContains(TestAPIKeyMethodWithRight::METHOD_NAME, $names);
        $this->assertContains(TestAPIKeyMethod::METHOD_NAME, $names);
    }

    // endregion

    // region: clearIndexCache()

    public function test_clearIndexCacheForcesReRead(): void
    {
        $index = $this->getIndex();
        $index->build();
        $index->clearIndexCache();

        // Populate the in-memory cache
        $this->assertTrue($index->methodExists(TestAPIKeyMethodWithRight::METHOD_NAME));

        // Tamper with the disk file: remove a method entry
        $data = $index->getDataFile()->getData();
        unset($data[APIMethodIndex::KEY_METHODS][TestAPIKeyMethodWithRight::METHOD_NAME]);
        $index->getDataFile()->putData($data);

        // Without clearing, the in-memory cache still has the method
        $this->assertTrue($index->methodExists(TestAPIKeyMethodWithRight::METHOD_NAME));

        // After clearing, the next read picks up the tampered file
        $index->clearIndexCache();
        $this->assertFalse($index->methodExists(TestAPIKeyMethodWithRight::METHOD_NAME));

        // Restore the index for other tests
        $index->clearIndexCache();
        $index->build();
    }

    // endregion
}
