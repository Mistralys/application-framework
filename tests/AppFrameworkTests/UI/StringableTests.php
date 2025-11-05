<?php
/**
 * @package AppFrameworkTests
 * @subpackage UI
 */

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\LegacyUIRenderableStub;
use AppFrameworkTestClasses\Stubs\StringableStub;
use stdClass;
use UI;
use UI_Exception;

/**
 * @package AppFrameworkTests
 * @subpackage UI
 *
 * @covers toString()
 * @covers UI::requireRenderable
 */
class StringableTests extends ApplicationTestCase
{
    public function test_objectIsNotARenderable() : void
    {
        $this->expectException(UI_Exception::class);
        $this->expectExceptionCode(UI::ERROR_NOT_A_RENDERABLE);

        toString(new stdClass());
    }

    public function test_resourceIsNotARenderable() : void
    {
        $this->expectException(UI_Exception::class);
        $this->expectExceptionCode(UI::ERROR_NOT_A_RENDERABLE);

        $handle = fopen('php://temp', 'rb');
        try {
            toString($handle);
        } finally {
            fclose($handle);
        }
    }

    public function test_booleanIsARenderable() : void
    {
        $this->assertSame('true', toString(true));
        $this->assertSame('false', toString(false));

        $this->addToAssertionCount(1);
    }

    public function test_integerIsARenderable() : void
    {
        $this->assertSame('42', toString(42));

        $this->addToAssertionCount(1);
    }

    public function test_floatIsARenderable() : void
    {
        $this->assertSame('3.14', toString(3.14));

        $this->addToAssertionCount(1);
    }

    public function test_legacyRenderableIsRenderable() : void
    {
        $this->assertSame(LegacyUIRenderableStub::RETURN_VALUE, toString(new LegacyUIRenderableStub()));
    }

    public function test_stringIsRenderable() : void
    {
        $this->assertSame('Hello, World!', toString('Hello, World!'));
    }

    public function test_stringableIsRenderable() : void
    {
        $this->assertSame(StringableStub::RETURN_VALUE, toString(new StringableStub()));
    }

    public function test_arrayIsNotARenderable() : void
    {
        $this->expectException(UI_Exception::class);
        $this->expectExceptionCode(UI::ERROR_NOT_A_RENDERABLE);

        toString(['not', 'a', 'renderable']);
    }

    public function test_buttonIsRenderable() : void
    {
        $this->assertNotEmpty(toString(UI::button('Click Me')));
    }
}
