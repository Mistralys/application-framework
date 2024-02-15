<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\Request;

/** @var string $activeURL The URL to this example */

$nav = UI_Page_Navigation::create('custom-nav')
    ->setTemplateID('navigation.subnav');

$nav->addClickable('Clickable link', "alert('Clicked!')");

$nav->addExternalLink('URL link', 'https://www.google.com');

// By default, the active item is determined automatically
// by comparing the current URL with that of the item.
$nav->addURL('Active item', $activeURL);

$nav->addDropdownMenu('Split drop down')
    ->link('#')
    ->addLink('Link 1', '#');

// The search simply redirects to the current page, but
// with a success message.
$nav->addSearch(function(UI_Page_Navigation_Item_Search $item, string $terms) use($activeURL) {
    AppFactory::createDriver()->redirectWithSuccessMessage(
        sprintf(
            'The search has been submitted: "%s"',
            htmlspecialchars($terms)
        ),
        $activeURL
    );
})
    ->addHiddenPageVars()
    ->addHiddenVar('example', Request::getInstance()->getParam('example'));

echo $nav;
