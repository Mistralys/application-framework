<?php
/* @var $this UI_Page_Template */

$form = $this->ui->createForm('example-sections');

$form->addHeader('First section');

$form->addText('text1', 'First element');
$form->addText('text2', 'Second element');

$form->addHeader('Second section');

$form->addText('text3', 'Third element');
$form->addText('text4', 'Fourth element');

echo $form->renderHorizontal();

?>