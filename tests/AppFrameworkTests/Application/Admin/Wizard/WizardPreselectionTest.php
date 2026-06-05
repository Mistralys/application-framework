<?php

declare(strict_types=1);

namespace testsuites\Application\Admin\Wizard;

use Application\Admin\Wizard\WizardPreselection;
use Application_Exception;
use PHPUnit\Framework\TestCase;
use TestDriver_Area_WizardTest_Wizard_Step_Countries;
use TestDriver_Area_WizardTest_Wizard_Step_Summary;

final class WizardPreselectionTest extends TestCase
{
    public function test_setAndGetStepValue(): void
    {
        $preselection = new WizardPreselection();
        $preselection->setStepValue('StepOne', 'color', 'blue');
        $preselection->setStepValue('StepOne', 'size', 'large');

        $values = $preselection->getStepValues('StepOne');

        $this->assertSame('blue', $values['color']);
        $this->assertSame('large', $values['size']);
        $this->assertSame(array(), $preselection->getStepValues('Nonexistent'));
    }

    public function test_hasStepValues(): void
    {
        $preselection = new WizardPreselection();

        $this->assertFalse($preselection->hasStepValues('StepOne'));

        $preselection->setStepValue('StepOne', 'foo', 'bar');

        $this->assertTrue($preselection->hasStepValues('StepOne'));
        $this->assertFalse($preselection->hasStepValues('StepTwo'));
    }

    public function test_toArray(): void
    {
        $preselection = new WizardPreselection();
        $preselection->setStepValue('StepA', 'key1', 'value1');
        $preselection->setStepValue('StepB', 'key2', 42);

        $expected = array(
            'StepA' => array('key1' => 'value1'),
            'StepB' => array('key2' => 42),
        );

        $this->assertSame($expected, $preselection->toArray());
    }

    public function test_isEmpty(): void
    {
        $preselection = new WizardPreselection();

        $this->assertTrue($preselection->isEmpty());

        $preselection->setStepValue('StepOne', 'foo', 'bar');

        $this->assertFalse($preselection->isEmpty());
    }

    public function test_fluentInterface(): void
    {
        $preselection = new WizardPreselection();

        $result = $preselection
            ->setStepValue('StepOne', 'a', 1)
            ->setStepValue('StepOne', 'b', 2)
            ->setStepValue('StepTwo', 'c', 3);

        $this->assertSame($preselection, $result);
        $this->assertSame(2, $preselection->getStepValues('StepOne')['b']);
        $this->assertSame(3, $preselection->getStepValues('StepTwo')['c']);
    }

    public function test_getStepNames(): void
    {
        $preselection = new WizardPreselection();

        $this->assertSame(array(), $preselection->getStepNames());

        $preselection->setStepValue('Alpha', 'x', true);
        $preselection->setStepValue('Beta', 'y', false);

        $this->assertSame(array('Alpha', 'Beta'), $preselection->getStepNames());
    }

    public function test_setStepValueByClass_resolvesFromConstant(): void
    {
        $preselection = new WizardPreselection();
        $preselection->setStepValueByClass(
            TestDriver_Area_WizardTest_Wizard_Step_Countries::class,
            'country_id',
            42
        );

        $values = $preselection->getStepValues('Countries');

        $this->assertSame(42, $values['country_id']);
    }

    public function test_setStepValueByClass_throwsWithoutConstant(): void
    {
        $preselection = new WizardPreselection();

        $this->expectException(Application_Exception::class);
        $this->expectExceptionCode(WizardPreselection::ERROR_STEP_CLASS_MISSING_STEP_NAME);

        $preselection->setStepValueByClass(
            TestDriver_Area_WizardTest_Wizard_Step_Summary::class,
            'key',
            'value'
        );
    }

    public function test_setStepValueByClass_fluentInterface(): void
    {
        $preselection = new WizardPreselection();

        $result = $preselection->setStepValueByClass(
            TestDriver_Area_WizardTest_Wizard_Step_Countries::class,
            'country_id',
            99
        );

        $this->assertSame($preselection, $result);
    }
}
