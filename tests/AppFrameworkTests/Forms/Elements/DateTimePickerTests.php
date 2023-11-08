<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms\Elements;

use AppFrameworkTestClasses\FormTestCase;
use DateTime;
use HTML_QuickForm2;
use HTML_QuickForm2_Element_HTMLDateTimePicker;

final class DateTimePickerTests extends FormTestCase
{
    public function test_setValidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');

        $el->setValue('2020-01-01 12:30');

        $this->assertSame('2020-01-01 12:30', $el->getDateString());
    }

    public function test_setInvalidDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');

        $el->setValue('not a date');

        $this->assertEmpty($el->getDateString());
    }

    public function test_setDateObject() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');

        $el->setValue(new DateTime('2020-06-22 12:30:45'));

        $this->assertSame('2020-06-22 12:30', $el->getDateString());
    }

    public function test_getDate() : void
    {
        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');

        $el->setValue(new DateTime('2020-06-22 12:30:45'));

        $date = $el->getDate();
        $this->assertNotNull($date);
        $this->assertSame('2020-06-22 12:30', $date->format('Y-m-d H:i'));
    }

    public function test_submitValidDateAsArray() : void
    {
        $_REQUEST[HTML_QuickForm2::resolveTrackVarName('test-create')] = 'yes';

        $_POST['picker'] = array(
            HTML_QuickForm2_Element_HTMLDateTimePicker::ELEMENT_NAME_DATE => '2020-06-21',
            HTML_QuickForm2_Element_HTMLDateTimePicker::ELEMENT_NAME_TIME => '12:30'
        );

        $form = new HTML_QuickForm2('test-create');

        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');
        $form->addElement($el);

        $this->assertCount(2, $el->getElements());

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->validate());

        $this->assertSame('2020-06-21', $el->getDateElement()->getValue());
        $this->assertSame('12:30', $el->getTimeElement()->getValue());
        $this->assertSame('2020-06-21 12:30', $el->getDateString());
    }

    public function test_submitValidDateAsString() : void
    {
        $_REQUEST[HTML_QuickForm2::resolveTrackVarName('test-create')] = 'yes';

        $_POST['picker'] = '2020-06-21 12:30';

        $form = new HTML_QuickForm2('test-create');

        $el = new HTML_QuickForm2_Element_HTMLDateTimePicker('picker');
        $form->addElement($el);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->validate());

        $this->assertSame('2020-06-21', $el->getDateElement()->getValue());
        $this->assertSame('12:30', $el->getTimeElement()->getValue());
        $this->assertSame('2020-06-21 12:30', $el->getDateString());
    }
}
