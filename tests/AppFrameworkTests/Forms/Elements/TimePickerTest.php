<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms\Elements;

use AppFrameworkTestClasses\FormTestCase;
use Application\UI\Form\Element\DateTimePicker\BasicTime;
use DateTime;
use HTML_QuickForm2;
use HTML_QuickForm2_Element_HTMLTimePicker;

final class TimePickerTest extends FormTestCase
{
    public function test_setValidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLTimePicker('picker');

        $el->setValue('18:45');

        $this->assertSame('18:45', $el->getValue());
        $this->assertNotNull($el->getTime());
        $this->assertSame(18, $el->getHour());
        $this->assertSame(45, $el->getMinutes());
    }

    public function test_setInvalidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLTimePicker('picker');

        $el->setValue('not a time');

        $this->assertSame('not a time', $el->getValue());
        $this->assertNull($el->getTime());
        $this->assertNull($el->getHour());
        $this->assertNull($el->getMinutes());
    }

    public function test_getTimeException() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLTimePicker('picker');

        $el->setValue('42:66');

        $this->expectExceptionCode(BasicTime::ERROR_INVALID_TIME);

        $el->getTime();
    }

    public function test_setDateObject() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLTimePicker('picker');

        $el->setValue(new DateTime('2023-05-16 18:45'));

        $this->assertSame('18:45', $el->getValue());
    }

    public function test_setTimeObject() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLTimePicker('picker');

        $el->setValue(new BasicTime(18, 45));

        $this->assertSame('18:45', $el->getValue());
    }

    public function test_validate() : void
    {
        $_REQUEST[HTML_QuickForm2::resolveTrackVarName('test-validate')] = 'yes';
        $_POST['valid-picker'] = '18:45';
        $_POST['invalid-picker'] = 'not a date';
        $_POST['unknown-picker'] = '42:66';

        $form = new HTML_QuickForm2('test-validate');

        $elValid = new HTML_QuickForm2_Element_HTMLTimePicker('valid-picker');
        $elEmpty = new HTML_QuickForm2_Element_HTMLTimePicker('empty-picker');
        $elInvalid = new HTML_QuickForm2_Element_HTMLTimePicker('invalid-picker');
        $elUnknown = new HTML_QuickForm2_Element_HTMLTimePicker('unknown-picker');

        $form->addElement($elValid);
        $form->addElement($elEmpty);
        $form->addElement($elInvalid);
        $form->addElement($elUnknown);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->validate());

        $this->assertNull($elValid->getError());
        $this->assertNull($elEmpty->getError());
        $this->assertStringContainsString('Not a valid time', $elInvalid->getError());
        $this->assertStringContainsString('time does not exist', $elUnknown->getError());
    }
}
