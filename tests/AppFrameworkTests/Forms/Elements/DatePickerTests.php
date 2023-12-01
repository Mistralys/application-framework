<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms\Elements;

use AppFrameworkTestClasses\FormTestCase;
use DateTime;
use HTML_QuickForm2;
use HTML_QuickForm2_Element_HTMLDatePicker;

/**
 * @see HTML_QuickForm2_Element_HTMLDatePicker
 */
final class DatePickerTests extends FormTestCase
{
    public function test_setValidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDatePicker('picker');

        $el->setValue('2020-11-24');

        $this->assertSame('2020-11-24', $el->getValue());
        $this->assertNotNull($el->getDate());
        $this->assertSame(2020, $el->getYear());
        $this->assertSame(11, $el->getMonth());
        $this->assertSame(24, $el->getDay());
    }

    public function test_setInvalidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDatePicker('picker');

        $el->setValue('not a date');

        $this->assertSame('not a date', $el->getValue());
        $this->assertNull($el->getDate());
        $this->assertNull($el->getYear());
    }

    public function test_setDateObject() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDatePicker('picker');

        $el->setValue(new DateTime('2023-05-16'));

        $this->assertSame('2023-05-16', $el->getValue());
    }

    public function test_getDateException() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDatePicker('picker');

        $el->setValue('2023-45-66');

        $this->expectExceptionCode(HTML_QuickForm2_Element_HTMLDatePicker::ERROR_INVALID_DATE_VALUE);

        $el->getDate();
    }

    public function test_validate() : void
    {
        $_REQUEST[HTML_QuickForm2::resolveTrackVarName('test-validate')] = 'yes';
        $_POST['valid-picker'] = '2020-06-23';
        $_POST['invalid-picker'] = 'not a date';
        $_POST['unknown-picker'] = '2020-80-99';

        $form = new HTML_QuickForm2('test-validate');

        $elValid = new HTML_QuickForm2_Element_HTMLDatePicker('valid-picker');
        $elEmpty = new HTML_QuickForm2_Element_HTMLDatePicker('empty-picker');
        $elInvalid = new HTML_QuickForm2_Element_HTMLDatePicker('invalid-picker');
        $elUnknown = new HTML_QuickForm2_Element_HTMLDatePicker('unknown-picker');

        $form->addElement($elValid);
        $form->addElement($elEmpty);
        $form->addElement($elInvalid);
        $form->addElement($elUnknown);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->validate());

        $this->assertNull($elValid->getError());
        $this->assertNull($elEmpty->getError());
        $this->assertStringContainsString('Not a valid date', $elInvalid->getError());
        $this->assertStringContainsString('date does not exist', $elUnknown->getError());
    }
}
