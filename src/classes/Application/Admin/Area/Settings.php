<?php

use AppLocalize\Localization;

class Application_Admin_Area_Settings extends Application_Admin_Area
{
    const URL_NAME = 'settings';

    /**
     * @var string
     */
    protected $formName = 'usersettings';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode() : string
    {
        return '';
    }

    public function getTitle() : string
    {
        return t('User settings');
    }

    public function getNavigationTitle() : string
    {
        return t('Settings');
    }

    public function isUserAllowed() : bool
    {
        return $this->user->canLogin();
    }

    public function getNavigationGroup() : string
    {
        return '';
    }
    
    public function getNavigationIcon() : ?UI_Icon
    {
        return UI::icon()->tools();
    }

    public function isCore() : bool
    {
        return true;
    }
    
    public function getDependencies() : array
    {
        return array();
    }
    
    protected function _handleActions() : bool
    {
        if($this->request->getBool('reset-usercache')) {
            $this->resetUsercache();
        }
        
        $this->createSettingsForm();

        if (!$this->isFormValid()) {
            return true;
        }

        $values = $this->getFormValues();

        $this->user->setSetting('locale', $values['settings']['locale']);
        $this->user->setSetting('layout_width', $values['settings']['layout_width']);
        $this->user->setSetting('startup_tab', $values['settings']['startup_tab']);
        $this->user->saveSettings();

        $this->redirectWithSuccessMessage(
            t('Your interface settings have been saved successfully at %1s.', sb()->time()),
            $this->user->getAdminSettingsURL()
        );
    }

    public function _renderContent()
    {
        return $this->renderForm(t('Your personal settings'), $this->formableForm);
    }

    protected function _handleHelp() : void
    {
        $this->renderer->getTitle()->setIcon($this->getNavigationIcon());
    }

    protected function _handleSidebar() : void
    {
        $this->sidebar->addButton('save', t('Save now'))
        ->setIcon(UI::icon()->save())
        ->makePrimary()
        ->makeClickableSubmit($this->formableForm);
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addButton('reset_usercache', t('Reset %1$s settings...', $this->driver->getAppNameShort()))
        ->setTooltip(t('Resets all your %1$s settings, including list filters.', $this->driver->getAppNameShort()))
        ->setIcon(UI::icon()->reset())
        ->makeClickable('application.dialogResetUsercache()');
    }
    
    private function createSettingsForm() : void
    {
        $defaultValues = array(
            'settings' => array(
                'locale' => $this->user->getSetting('locale'),
                'layout_width' => $this->user->getSetting('layout_width'),
                'startup_tab' => $this->user->getSetting('startup_tab', $this->driver->getAppSet()->getDefaultArea()->getURLName())
            )
        );

        $this->createFormableForm($this->formName, $defaultValues);

        $settings = $this->formableForm->addTab('settings', t('User interface options'));
        $locale = Localization::injectAppLocalesSelector('locale', $settings);
        $locale->addClass('input-xlarge');
        
        $startup = $this->addElementSelect('startup_tab', t('Startup tab'), $settings);
        $startup->setComment(t('Lets you choose which %1$s tab to open by default when you log in.', $this->driver->getAppNameShort()));
        $areas = $this->driver->getAllowedAreas();
        foreach($areas as $area) {
            $startup->addOption($area->getTitle(), $area->getURLName());
        }
        
        $lw = $this->addElementSelect('layout_width', t('Layout width'), $settings);
        $lw->addClass('input-xxlarge');
        $lw->addOption(t('Standard'), 'standard');
        $lw->addOption(t('Maximized (recommended for small screens)'), 'maximized');
        $lw->setComment(
            t('Allows you to choose how wide the %1$s interface should be.', $this->driver->getAppNameShort()). ' ' .
            t('For example if you work on a smaller screen (laptop, tablet...), you can select the maximized width to use more of the available space.')
        );

        $rights = $this->formableForm->addTab('rights', t('Roles summary'));
        $rightsHTML = $this->renderTemplate(
            'content.settings.roles',
            array(
                'user' => $this->user
            )
        );
        $this->addElementHTML($rightsHTML, $rights);
    }
    
    protected function resetUsercache() : void
    {
        $this->startTransaction();
        $this->user->resetSettings();
        $this->endTransaction();
        
        $this->redirectWithSuccessMessage(
            t('Your user settings have been successfully reset at %1$s.', date('H:i:s')), 
            $this->getURL()
        );
    }
}