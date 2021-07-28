<?php
/**
 * File containing the template class {@see template_default_frame_header_user_menu}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_header_user_menu
 */

declare(strict_types=1);

/**
 * Template for the user's drop down menu.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_frame_header_user_menu extends UI_Page_Template_Custom
{
    /**
     * @var bool
     */
    private $showUserName = false;

    protected function generateOutput() : void
    {
        $menu = $this->ui->createButtonDropdown()
            ->makeNavItem()
            ->noCaret()
            ->setIcon(UI::icon()->user());

        if($this->showUserName)
        {
            $menu->setLabel($this->user->getName());
        }

        $menu->addLink(t('Quickstart'), $this->user->getRecent()->getAdminURL())
            ->setTitle(t('Shows your personal quickstart screen with recently visited elements.'))
            ->setIcon(UI::icon()->home());

        $menu->addClickable(t('Notepad'), Application_User_Notepad::getJSOpen())
            ->setTitle(Application_User_Notepad::getTooltipText())
            ->setIcon(UI::icon()->notepad());

        $menu->addLink(t('Settings'), $this->user->getAdminSettingsURL())
            ->setTitle(t('Opens your personal %1$s settings.', $this->driver->getAppNameShort()))
            ->setIcon(UI::icon()->tools());

        $menu->addSeparator();

        $menu->addHeader(t('Interface width'));

        $std = $menu->addLink(t('Standard'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_width', 'value' => 'standard')));
        $max = $menu->addLink(t('Maximized'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_width', 'value' => 'maximized')));

        if ($this->user->getSetting('layout_width', 'standard') == 'maximized')
        {
            $std->setIcon(UI::icon()->itemInactive());
            $max->setIcon(UI::icon()->itemActive());
        }
        else
        {
            $std->setIcon(UI::icon()->itemActive());
            $max->setIcon(UI::icon()->itemInactive());
        }

        $menu->addHeader(t('Interface font size'));

        $std = $menu->addLink(t('Standard'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_fontsize', 'value' => 'standard')));
        $max = $menu->addLink(t('Bigger'), $this->request->buildRefreshURL(array(Application_Bootstrap_Screen::REQUEST_PARAM_SET_USERSETTING => 'layout_fontsize', 'value' => 'bigger')));

        if ($this->user->getSetting('layout_fontsize', 'standard') == 'bigger')
        {
            $std->setIcon(UI::icon()->itemInactive());
            $max->setIcon(UI::icon()->itemActive());
        }
        else
        {
            $std->setIcon(UI::icon()->itemActive());
            $max->setIcon(UI::icon()->itemInactive());
        }

        $menu->addSeparator();

        $menu->addLink(t('Log out'), '?logout=yes')
            ->setTitle(t('Click here to end your session and log out.'))
            ->setIcon(UI::icon()->logOut());

        $menu->display();
    }

    protected function preRender() : void
    {
        $this->showUserName = Application_Driver::getBoolSetting(UI_Themes::OPTION_SHOW_USER_NAME);
    }
}
