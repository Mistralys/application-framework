<?php

/* @var $this UI_Page_Template */

$menu = $this->ui->createButtonDropdown()
->makeNavItem()
->noCaret()
->setIcon(UI::icon()->user())
->setLabel($this->user->getName());

$menu->addLink(t('Settings'), '?page=settings')
    ->setTitle(t('Opens your %1$s user settings.', $this->driver->getAppNameShort()))
    ->setIcon(UI::icon()->settings());

$menu->addClickable(t('Notepad'), 'Driver.DialogNotepad()')
    ->setTitle(t('Opens your personal notepad.'))
    ->setIcon(UI::icon()->notepad());

$menu->addSeparator();

$menu->addHeader(t('Interface width'));

$std = $menu->addLink(t('Standard'), $this->request->buildRefreshURL(array('set_usersetting' => 'layout_width', 'value' => 'standard')));
$max = $menu->addLink(t('Maximized'), $this->request->buildRefreshURL(array('set_usersetting' => 'layout_width', 'value' => 'maximized')));

if($this->user->getSetting('layout_width', 'standard') == 'maximized') {
    $std->setIcon(UI::icon()->itemInactive());
    $max->setIcon(UI::icon()->itemActive());
} else {
    $std->setIcon(UI::icon()->itemActive());
    $max->setIcon(UI::icon()->itemInactive());
}

$menu->addHeader(t('Interface font size'));

$std = $menu->addLink(t('Standard'), $this->request->buildRefreshURL(array('set_usersetting' => 'layout_fontsize', 'value' => 'standard')));
$max = $menu->addLink(t('Bigger'), $this->request->buildRefreshURL(array('set_usersetting' => 'layout_fontsize', 'value' => 'bigger')));

if($this->user->getSetting('layout_fontsize', 'standard') == 'bigger') {
    $std->setIcon(UI::icon()->itemInactive());
    $max->setIcon(UI::icon()->itemActive());
} else {
    $std->setIcon(UI::icon()->itemActive());
    $max->setIcon(UI::icon()->itemInactive());
}

$menu->addSeparator();

$menu->addLink(t('Log out'), '?logout=yes')
->setTitle(t('Click here to end your session and log out.'))
->setIcon(UI::icon()->logOut());

$menu->display();