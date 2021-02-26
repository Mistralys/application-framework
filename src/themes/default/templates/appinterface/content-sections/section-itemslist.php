<?php
/* @var $this UI_Page_Template */

$section = $this->createSection();

$list = $section->addItemsSelector();
$list->addItem('IONOS', 'http://www.ionos.de', 'The IONOS homepage and shopping central for all IONOS products.');
$list->addItem('United Internet', 'http://united-internet.de', 'The homepage of the United Internet Brand.');
$list->addItem('United Internet for UNICEF', 'http://united-internet-for-unicef-stiftung.de', 'The foundation for helping UNICEF fund its campaigns to help children accross the globe.');

$section->display();

?>