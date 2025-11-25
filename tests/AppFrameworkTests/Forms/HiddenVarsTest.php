<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\HiddenVariablesStub;

final class HiddenVarsTest extends ApplicationTestCase
{
    // region: _Tests

    public function test_privateVar() : void
    {
        $this->stub->addPrivateVar('test', 'value');

        $this->assertArrayNotHasKey('test', $this->stub->getHiddenVars());
        $this->assertStringContainsString('name="test"', $this->stub->renderHiddenInputs(), 'The rendered HTML must contain the private variables.');
    }

    public function test_divHasClasses() : void
    {
        $this->assertStringContainsString(
            'class="foo hiddens test"',
            $this->stub->renderHiddenInputs(array('test', 'foo'))
        );
    }

    public function test_hiddenElementsHaveAnID() : void
    {
        $this->assertStringContainsString(
            'id="foobar"',
            $this->stub
                ->addHiddenVar('test', 'value', 'foobar')
                ->renderHiddenInputs()
        );
    }

    public function test_getHiddenVariables() : void
    {
        $this->stub->addHiddenVar('string', 'string');
        $this->stub->addHiddenVar('int', 42);
        $this->stub->addHiddenVar('float', 3.14);
        $this->stub->addHiddenVar('null', null);
        $this->stub->addHiddenVar('stringable', sb()->add('stringable'));

        $vars = $this->stub->getHiddenVars();

        $this->assertArrayHasKey('string', $vars);
        $this->assertArrayHasKey('int', $vars);
        $this->assertArrayHasKey('float', $vars);
        $this->assertArrayHasKey('null', $vars);
        $this->assertArrayHasKey('stringable', $vars);

        $this->assertSame('string', $vars['string']);
        $this->assertSame('42', $vars['int']);
        $this->assertSame('3.14', $vars['float']);
        $this->assertSame('', $vars['null']);
        $this->assertSame('stringable', $vars['stringable']);
    }

    // endregion

    // region: Support methods

    private HiddenVariablesStub $stub;

    protected function setUp() : void
    {
        $this->stub = new HiddenVariablesStub();
    }

    // endregion
}
