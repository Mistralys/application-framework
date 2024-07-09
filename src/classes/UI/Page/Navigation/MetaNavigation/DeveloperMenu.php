<?php

declare(strict_types=1);

namespace UI\Page\Navigation\MetaNavigation;

use Application;
use Application\Environments;
use Application_Driver;
use Application_LockManager;
use Application_Request;
use Application_Session_Base;
use Application_Session_Native;
use Application_User;
use UI;
use UI_Page_Navigation_Item_DropdownMenu;

class DeveloperMenu
{
    protected UI_Page_Navigation_Item_DropdownMenu $menu;
    private bool $configured = false;
    private Application_Request $request;
    private Application_User $user;

    public function __construct(UI_Page_Navigation_Item_DropdownMenu $menu)
    {
        $this->menu = $menu;
        $this->request = Application_Driver::getInstance()->getRequest();
        $this->user = Application::getUser();
    }

    public function configure() : void
    {
        if($this->configured) {
            return;
        }

        $this->configured = true;

        $menu = $this->menu->getMenu();

        $menu->addHeader(t('Settings'));

        $menu->addClickable(t('Clientside logging settings'), 'application.dialogLogging()')
            ->setIcon(UI::icon()->settings());

        $menu->addClickable(t('Icons reference sheet'), 'UI.Icon().DialogReferenceSheet()')
            ->setIcon(UI::icon()->view());

        $menu->addSeparator();
        $menu->addHeader(t('Application mode'));

        if($this->user->isDeveloper()) {
            $disable = $menu->addLink(t('Regular'), $this->request->buildRefreshURL(array('develmode_enable' => 'no')));
            $enable = $menu->addLink(t('Developer'), $this->request->buildRefreshURL(array('develmode_enable' => 'yes')));

            if ($this->user->isDeveloperModeEnabled()) {
                $enable->setIcon(UI::icon()->itemActive());
                $disable->setIcon(UI::icon()->itemInactive());
            } else {
                $enable->setIcon(UI::icon()->itemInactive());
                $disable->setIcon(UI::icon()->itemActive());
            }
        } else {
            $menu->addClickable(t('Developer'), "alert('".t('Switch back to a developer role to disable.')."')")
                ->setIcon(UI::icon()->itemActive());
        }

        if(isDevelMode())
        {
            $session = Application::getSession();
            $current = $session->getRightPreset();
            $currentRights = $session->fetchSimulatedRights();

            sort($currentRights);

            $menu->addSeparator();
            $menu->addHeader(t('Lock manager'));

            $enabled = $menu->addLink(t('Enabled'), $this->request->buildRefreshURL(array('lockmanager_enable' => 'yes')));
            $disabled = $menu->addLink(t('Disabled'), $this->request->buildRefreshURL(array('lockmanager_enable' => 'no')));

            if(Application_LockManager::isEnabled()) {
                $enabled->setIcon(UI::icon()->itemActive());
                $disabled->setIcon(UI::icon()->itemInactive());
            } else {
                $enabled->setIcon(UI::icon()->itemInactive());
                $disabled->setIcon(UI::icon()->itemActive());
            }

            $menu->addSeparator();
            $menu->addHeader(t('Rolesets'));

            $presets = $session->getRightPresets();
            foreach ($presets as $preset)
            {
                $link = $menu->addLink(
                    $preset->getLabel(),
                    $this->request->buildRefreshURL(array(
                        Application_Session_Base::REQUEST_PARAM_RIGHTS_PRESET => $preset->getID()
                    )
                ))
                    ->setTitle(implode(', ', $preset->getRightIDs()));

                if ($preset->getID() === $current) {
                    $link->setIcon(UI::icon()->itemActive());
                } else {
                    $link->setIcon(UI::icon()->itemInactive());
                }
            }
        }
    }
}

