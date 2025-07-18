<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('example-elements');

foreach($form->getCustomElements() as $def)
{
    $el = $form->getForm()->addElement($def['alias'], $def['alias']);
    $el->setRuntimeProperty(UI_Form::PROPERTY_DEMO_MODE, true);
    $el->setLabel($def['name']);
    $el->setComment('Type: '.$def['alias']);
}

echo $form;
