<?php

final class Forms_DefaultValuesTest extends ApplicationTestCase
{
    public function test_default(): void
    {
        $this->startTest('Form default values on create');

        $form = UI::getInstance()->createForm('somename', array('foo' => 'bar'));
        $el = $form->addText('foo', 'Foo');

        $this->assertEquals('bar', $el->getValue());
    }

    public function test_default_afterCreate(): void
    {
        $this->enableLogging();

        $this->startTest('Form default values after create');

        $form = UI::getInstance()->createForm('somename');
        $form->setDefaultValues(array('foo' => 'bar'));

        $el = $form->addText('foo', 'Foo');

        $this->assertEquals('bar', $el->getValue());
    }

    public function test_default_afterElement(): void
    {
        $this->enableLogging();

        $this->startTest('Form default values after adding an element');

        $form = UI::getInstance()->createForm('somename');

        $el = $form->addText('foo', 'Foo');

        $form->setDefaultValues(array('foo' => 'bar'));

        // Elements get their value passed on initialization. This means
        // that setting the default values after adding elements will not
        // actually do anything.
        $this->assertEquals(null, $el->getValue());
    }
}