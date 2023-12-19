<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('example-elements');

$custom = $form->getCustomElements();

foreach($custom as $def)
{
    $el = $form->getForm()->addElement($def['alias'], $def['alias']);
    $el->setRuntimeProperty('demo', true);
    $el->setLabel($def['name']);
    $el->setComment('Type: '.$def['alias']);
}

echo $form;
