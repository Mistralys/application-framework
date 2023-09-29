<?php

declare(strict_types=1);

namespace UI\Page\Navigation\MetaNavigation;

use Application;
use Application_Bootstrap_Screen;
use Application_Driver;
use Application_Request;
use Application_User;
use Application_User_Notepad;
use UI;
use UI_Bootstrap_DropdownMenu;
use UI_Page_Navigation_Item_DropdownMenu;
use UI_Themes;

class UserMenu
{
    protected UI_Page_Navigation_Item_DropdownMenu $dropdown;
    private Application_User $user;
    private UI_Bootstrap_DropdownMenu $menu;
    private Application_Driver $driver;
    private Application_Request $request;

    public function __construct(UI_Page_Navigation_Item_DropdownMenu $dropdown)
    {
        $this->user = Application::getUser();
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();
        $this->dropdown = $dropdown;
        $this->menu = $dropdown->getMenu();
    }

    public function configure() : void
    {
        // Add the user's name as tooltip and header
        $this->dropdown->setTooltip(UI::tooltip(t('Logged in as %1$s.', $this->user->getName()))->makeLeft());
        $this->dropdown->addHeader($this->user->getName());

        $this->addQuickstart();
        $this->addNotepad();
        $this->addSettings();

        $this->dropdown->addSeparator();

        $this->addInterfaceWidth();
        $this->addInterfaceFontSize();

        $this->dropdown->addSeparator();

        $this->dropdown->addLink(t('Log out'), '?logout=yes')
            ->setTitle(t('Click here to end your session and log out.'))
            ->setIcon(UI::icon()->logOut());
    }

    private function addQuickstart() : void
    {
        $this->menu->addLink(t('Quickstart'), $this->user->getRecent()->getAdminURL())
            ->setTitle(t('Shows your personal quickstart screen with recently visited elements.'))
            ->setIcon(UI::icon()->home());
    }

    private function addNotepad() : void
    {
        $this->menu->addClickable(t('Notepad'), Application_User_Notepad::getJSOpen())
            ->setTitle(Application_User_Notepad::getTooltipText())
            ->setIcon(UI::icon()->notepad());
    }

    private function addSettings() : void
    {
        $this->menu->addLink(t('Settings'), $this->user->getAdminSettingsURL())
            ->setTitle(t('Opens your personal %1$s settings.', $this->driver->getAppNameShort()))
            ->setIcon(UI::icon()->tools());
    }

    private function addInterfaceFontSize() : void
    {
        $this->menu->addHeader(t('Interface font size'));

        $std = $this->dropdown->addLink(t('Standard'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_fontsize', 'value' => 'standard')));
        $max = $this->dropdown->addLink(t('Bigger'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_fontsize', 'value' => 'bigger')));

        if ($this->user->getSetting('layout_fontsize', 'standard') === 'bigger')
        {
            $std->setIcon(UI::icon()->itemInactive());
            $max->setIcon(UI::icon()->itemActive());
        }
        else
        {
            $std->setIcon(UI::icon()->itemActive());
            $max->setIcon(UI::icon()->itemInactive());
        }
    }

    private function addInterfaceWidth() : void
    {
        $this->menu->addHeader(t('Interface width'));

        $std = $this->dropdown->addLink(t('Standard'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_width', 'value' => 'standard')));
        $max = $this->dropdown->addLink(t('Maximized'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_width', 'value' => 'maximized')));

        if ($this->user->getSetting('layout_width', 'standard') === 'maximized')
        {
            $std->setIcon(UI::icon()->itemInactive());
            $max->setIcon(UI::icon()->itemActive());
        }
        else
        {
            $std->setIcon(UI::icon()->itemActive());
            $max->setIcon(UI::icon()->itemInactive());
        }
    }
}
