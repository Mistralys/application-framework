<?php
/* @var $this UI_Page_Template */

$form = $this->ui->createForm('example-elements');

$custom = $form->getCustomElements();

foreach($custom as $def) {
    $el = $form->getForm()->addElement($def['alias'], $def['alias']);
    $el->setRuntimeProperty('demo', true);
    $el->setLabel($def['name']);
    $el->setComment('Type: '.$def['alias']);
}

echo $form->renderHorizontal();

?>