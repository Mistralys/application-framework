<?php

    /* @var $this UI_Page_Template */

    $session = Application::getSession();
  
    $simulatedRights = false;
    $preset = $session->getValue(Application_Session_Base::KEY_NAME_RIGHTS_PRESET);
    if(!empty($preset)) {
        $simulatedRights = true;
    }
    
    if(!$this->user->isDeveloper() && !$simulatedRights) {
        return;
    }
    
    $menu = $this->ui->createButtonDropdown()
    ->makeNavItem()
    ->noCaret()
    ->setLabel(UI::icon()->developer());

    $menu->addHeader(t('Settings'));
    
    $menu->addClickable(t('Clientside logging settings'), 'application.dialogLogging()')
    ->setIcon(UI::icon()->settings());

    $menu->addClickable(t('Icons reference sheet'), 'UI.Icon().DialogReferenceSheet()')
    ->setIcon(UI::icon()->view());

    $menu->addSeparator();
    $menu->addHeader(t('Application mode'));
    
    $disable = $menu->addLink(t('Regular'), $this->request->buildRefreshURL(array('develmode_enable' => 'no')));
    $enable = $menu->addLink(t('Developer'), $this->request->buildRefreshURL(array('develmode_enable' => 'yes')));
    
    if($this->user->getSetting('developer_mode')=='yes') {
        $enable->setIcon(UI::icon()->itemActive());
        $disable->setIcon(UI::icon()->itemInactive());
    } else {
        $enable->setIcon(UI::icon()->itemInactive());
        $disable->setIcon(UI::icon()->itemActive());
    }
    
    if(Application::isSessionSimulated()) 
    {
        $current = $session->getRightPreset();
        $currentRights = explode(',', $session->getCurrentRights());
        
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
        foreach ($presets as $presetName => $rights) 
        {
            if(is_array($rights)) {
                $rights = implode(',', $rights);
            }
            
            $link = $menu->addLink(
                $presetName,
                $this->request->buildRefreshURL(array(
                    Application_Session_Base::KEY_NAME_RIGHTS_PRESET => $presetName
                )
            ))
            ->setTitle($rights);
            
            if ($presetName == $current) {
                $link->setIcon(UI::icon()->itemActive());
            } else {
                $link->setIcon(UI::icon()->itemInactive());
            }
        }
    
        if($session instanceof Application_Session_Native)
        {
            $menu->addSeparator();
            $menu->addHeader(t('Users'));
            
            $users = $session->getSimulateableUsers();
            
            foreach($users as $suid => $name) 
            {
                $link = $menu->addLink(
                    $name,
                    $this->request->buildRefreshURL(array(
                        Application_Session_Base::KEY_NAME_SIMULATED_ID => $suid)
                    )
                );

                if($this->user->getID() == $suid) {
                    $link->setIcon(UI::icon()->itemActive());
                } else {
                    $link->setIcon(UI::icon()->itemInactive());
                }
            }
        }
    }
    
    $menu->display();
