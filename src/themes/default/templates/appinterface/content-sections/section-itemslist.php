<?php
/* @var $this UI_Page_Template */

$section = $this->createSection();

$list = $section->addItemsSelector();
$list->addItem('App framework', 'https://github.com/Mistralys/application-framework', 'The framework code repository homepage.');

$section->display();

?>
