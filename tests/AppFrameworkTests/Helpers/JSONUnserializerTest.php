<?php

declare(strict_types=1);

namespace AppFrameworkTests\Helpers;

use AppFrameworkTestClasses\ApplicationTestCase;
use Mistralys\AppFramework\Helpers\JSONUnserializer;
use Mistralys\AppFramework\Helpers\JSONUnserializerException;

final class JSONUnserializerTest extends ApplicationTestCase
{
    public function test_createWithValidJSON() : void
    {
        $unserializer = JSONUnserializer::create('{"key":"value"}', 'Test context');

        $this->assertSame('Test context', $unserializer->getOperationContext());
        $this->assertSame(['key' => 'value'], $unserializer->getData());
        $this->assertNull($unserializer->getException());
    }

    public function test_createWithInvalidJSON() : void
    {
        $unserializer = JSONUnserializer::create('{"key":value}', 'Test context', false);

        $exception = $unserializer->getException();

        $this->assertNull($unserializer->getData());
        $this->assertNotNull($exception);
        $this->assertSame(JSONUnserializerException::ERROR_CANNOT_UNSERIALIZE_RESPONSE, $exception->getCode());
        $this->assertTrue($exception->isLoggingEnabled());
        $this->assertTrue($exception->isLogged());
    }

    public function test_createWithInvalidJSON_throws() : void
    {
        $this->expectExceptionCode(JSONUnserializerException::ERROR_CANNOT_UNSERIALIZE_RESPONSE);

        JSONUnserializer::create('{"key":value}', 'Test context', true);
    }
}
