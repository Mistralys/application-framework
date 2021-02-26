<?php
/* @var $this UI_Page_Template */

$form = $this->ui->createForm('example-sections');

$tab1 = $form->addTab('tab1', 'First tab');
$tab1->addText('text1')->setLabel('First element');
$tab1->addText('text2')->setLabel('Second element');

$tab2 = $form->addTab('tab2', 'Second tab');
$tab2->addText('text3')->setLabel('Third element');
$tab2->addText('text4')->setLabel('Fourth element');

echo $form->renderHorizontal();

?>