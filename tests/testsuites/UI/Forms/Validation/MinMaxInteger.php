<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestSuites\UI\Forms\Validation;

use Application_Formable_Generic;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Rule_Callback;
use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;

final class MinMaxInteger extends ApplicationTestCase
{
    // region: _Tests

    public function test_minimumInvalid() : void
    {
        $el = $this->createTestElement(40);
        $el->setValue('20');

        $this->assertFalse($this->resolveRule($el)->validate());
    }

    public function test_maximumInvalid() : void
    {
        $el = $this->createTestElement(null, 40);
        $el->setValue('100');

        $this->assertFalse($this->resolveRule($el)->validate());
    }

    public function test_minimumValid() : void
    {
        $el = $this->createTestElement(40);
        $el->setValue('40');

        $this->assertTrue($this->resolveRule($el)->validate());
    }

    public function test_maximumValid() : void
    {
        $el = $this->createTestElement(null, 40);
        $el->setValue('40');

        $this->assertTrue($this->resolveRule($el)->validate());
    }

    public function test_minMaxValid() : void
    {
        $el = $this->createTestElement(40, 40);
        $el->setValue('40');

        $this->assertTrue($this->resolveRule($el)->validate());
    }

    // endregion

    // region: Support methods

    private function resolveRule(HTML_QuickForm2_Element_InputText $el) : HTML_QuickForm2_Rule_Callback
    {
        $rules = $el->getRules();

        $this->assertNotEmpty($rules);

        $rule = array_shift($rules);
        $this->assertInstanceOf(HTML_QuickForm2_Rule_Callback::class, $rule);

        return $rule;
    }

    private function createTestElement(?int $min=null, ?int $max=null) : HTML_QuickForm2_Element_InputText
    {
        $formable = $this->createTestForm();

        $el = $formable->addElementText('element', t('Element'));

        $formable->makeMinMax($el, $min, $max);

        return $el;
    }

    private function createTestForm() : Application_Formable_Generic
    {
        $formable = new Application_Formable_Generic();
        $formable->createFormableForm('test-form-'.$this->getTestCounter());

        return $formable;
    }

    // endregion
}
