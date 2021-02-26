<?php
/* @var $this UI_Page_Template */

$this->createSection()
->setTitle('Section with context buttons')
->setContent('<p>Some content here.</p>')
->addContextButton(
    UI::button('Settings')
    ->setIcon(UI::icon()->settings())
)
->addContextButton(
    UI::button('Options')
    ->setIcon(UI::icon()->options())
)
->display();
