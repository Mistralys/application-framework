<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\API\Clients\Keys\APIKeyException;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;

final class KeyMethodsTest extends APIClientTestCase
{
    // region: _Tests

    public function test_add_and_has_method(): void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $available = $methods->getAvailableMethods();
        $this->assertNotEmpty($available, 'There should be available methods to test with');

        $method = $available[0];

        // initially not granted
        $this->assertFalse($methods->hasMethod($method));

        $methods->addMethod($method);

        $this->assertTrue($methods->hasMethod($method));
        $this->assertContains($method, $methods->getMethodNames());
    }

    public function test_add_methods_and_remove_methods(): void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $available = $methods->getAvailableMethods();
        $this->assertGreaterThanOrEqual(2, count($available), 'Need at least 2 available methods for this test');

        $subset = array_slice($available, 0, 2);

        $methods->addMethods($subset);

        foreach ($subset as $m) {
            $this->assertTrue($methods->hasMethod($m));
        }

        // remove one
        $methods->removeMethod($subset[0]);
        $this->assertFalse($methods->hasMethod($subset[0]));
        $this->assertTrue($methods->hasMethod($subset[1]));

        // removeMethods
        $methods->removeMethods([$subset[1]]);
        $this->assertFalse($methods->hasMethod($subset[1]));
    }

    public function test_set_methods_replaces_existing(): void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $available = $methods->getAvailableMethods();
        $this->assertGreaterThanOrEqual(3, count($available));

        $methods->setMethods([$available[0], $available[1]]);
        $names = $methods->getMethodNames();
        $this->assertCount(2, $names);

        // replace with a different set
        $methods->setMethods([$available[2]]);
        $this->assertCount(1, $methods->getMethodNames());
        $this->assertTrue($methods->hasMethod($available[2]));
    }

    public function test_grant_all_clears_individual_and_returns_all(): void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $available = $methods->getAvailableMethods();
        $this->assertNotEmpty($available);

        // add a single method
        $methods->addMethod($available[0]);
        $this->assertTrue($methods->hasMethod($available[0]));

        // grant all via key
        $key->setGrantAll(true);

        $this->assertTrue($key->areAllMethodsGranted());
        $this->assertTrue($methods->areAllGranted());

        $names = $methods->getMethodNames();
        $this->assertNotEmpty($names);
        $this->assertSame($available, $names);
    }

    public function test_adding_invalid_method_throws_exception(): void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $this->expectException(APIKeyException::class);

        // pick an obviously invalid method name
        $methods->addMethod('this.method.does.not.exist');
    }

    // endregion
}
