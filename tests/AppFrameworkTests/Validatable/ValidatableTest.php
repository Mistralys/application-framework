<?php

declare(strict_types=1);

namespace AppFrameworkTests\Validatable;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\ValidatableStub;

final class ValidatableTest extends ApplicationTestCase
{
    public function test_noError() : void
    {
        $stub = new ValidatableStub();

        $this->assertTrue($stub->isValid());
        $this->assertNull($stub->getValidationMessage());
        $this->assertNull($stub->getValidationCode());
    }

    public function test_error() : void
    {
        $stub = new ValidatableStub(ValidatableStub::ERROR_CODE);

        $this->assertFalse($stub->isValid());
        $this->assertSame(ValidatableStub::MESSAGE, $stub->getValidationMessage());
        $this->assertSame(ValidatableStub::ERROR_CODE, $stub->getValidationCode());
    }
}
