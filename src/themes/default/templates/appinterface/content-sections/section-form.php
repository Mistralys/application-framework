<?php
/* @var $this UI_Page_Template */

$form = $this->ui->createForm('dummy-section-form');
$form->addAlias('alias');

$section = $this->createSection();
$section->addForm($form);
$section->display();

?>