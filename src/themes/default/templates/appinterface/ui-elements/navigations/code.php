<?php

declare(strict_types=1);

use AppUtils\Request;

$nav = new UI_Page_Navigation(UI::getInstance()->getPage(), 'subnav');

$nav->addClickable('Clickable link', "alert('Clicked!')");
$nav->addExternalLink('URL link', 'https://www.google.com');
$nav->addDropdownMenu('Split drop down')
    ->link('#')
    ->addLink('Link 1', '#');
$nav->addSearch(function() {die('Search submitted');})
    ->addHiddenPageVars()
    ->addHiddenVar('example', Request::getInstance()->getParam('example'));


echo $nav;
