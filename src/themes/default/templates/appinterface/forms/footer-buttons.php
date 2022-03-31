<?php
/* @var $this UI_Page_Template */

$form = $this->ui->createForm('example-elements');

$form->addText('text', t('Text input'));

$form->addPrimarySubmit(t('Primary button'), 'primary');

$form->addSubmit(t('Secondary button'), 'secondary');

$form->display();
?>