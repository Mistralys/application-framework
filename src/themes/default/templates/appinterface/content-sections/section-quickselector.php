<?php
/* @var $this UI_Page_Template */

$section = $this->createSection();
$section->setTitle('Section title');
$section->setContent('<p>Some content here</p>');

$selector = $section->addQuickSelector();
$selector->addItem('one', t('First item'), $this->page->getURL());
$selector->addItem('two', t('Second item'), $this->page->getURL());

$section->display();

?>