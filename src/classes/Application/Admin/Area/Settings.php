<?php

declare(strict_types=1);

use Application\User\LayoutWidths;
use AppLocalize\Localization;
use AppUtils\ConvertHelper;

class Application_Admin_Area_Settings extends Application_Admin_Area
{
    public const URL_NAME = 'settings';
    public const REQUEST_PARAM_RESET_USERCACHE = 'reset-usercache';

    public const SETTING_LOCALE = 'locale';
    public const SETTING_LAYOUT_WIDTH = 'layout_width';
    public const SETTING_STARTUP_TAB = 'startup_tab';
    public const SETTING_DARK_MODE = 'dark_mode';

    protected string $formName = 'usersettings';

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
        return t('User settings');
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
        if($this->request->getBool(self::REQUEST_PARAM_RESET_USERCACHE)) {
            $this->resetUsercache();
        }
        
        $this->createSettingsForm();

        if (!$this->isFormValid()) {
            return true;
        }

        $values = $this->getFormValues();

        $this->user->setUILocale(Localization::getAppLocaleByName($values['settings'][self::SETTING_LOCALE]));
        $this->user->setLayoutWidth(LayoutWidths::getInstance()->getByID($values['settings'][self::SETTING_LAYOUT_WIDTH]));
        $this->user->setStartupScreenName($values['settings'][self::SETTING_STARTUP_TAB]);
        $this->user->setDarkModeEnabled(ConvertHelper::string2bool($values['settings'][self::SETTING_DARK_MODE]));
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
                self::SETTING_LOCALE => $this->user->getUILocale()->getName(),
                self::SETTING_LAYOUT_WIDTH => $this->user->getLayoutWidth()->getID(),
                self::SETTING_STARTUP_TAB => $this->user->getStartupScreenName(),
                self::SETTING_DARK_MODE => ConvertHelper::boolStrict2string($this->user->isDarkModeEnabled())
            )
        );

        $this->createFormableForm($this->formName, $defaultValues);

        $settings = $this->formableForm->addTab('settings', t('User interface options'));

        $this->injectUILocale($settings);
        $this->injectStartupTab($settings);
        $this->injectLayoutWidth($settings);
        $this->injectDarkMode($settings);

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

    /**
     * @param HTML_QuickForm2_Container_Group $container
     * @return void
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    protected function injectStartupTab(HTML_QuickForm2_Container_Group $container) : void
    {
        $el = $this->addElementSelect(self::SETTING_STARTUP_TAB, t('Startup tab'), $container);
        $el->setComment(t('Lets you choose which %1$s tab to open by default when you log in.', $this->driver->getAppNameShort()));
        $areas = $this->driver->getAllowedAreas();
        foreach ($areas as $area)
        {
            $el->addOption($area->getTitle(), $area->getURLName());
        }
    }

    /**
     * @param HTML_QuickForm2_Container_Group $container
     * @return void
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    protected function injectLayoutWidth(HTML_QuickForm2_Container_Group $container) : void
    {
        $el = $this->addElementSelect(self::SETTING_LAYOUT_WIDTH, t('Layout width'), $container);
        $el->addClass('input-xxlarge');
        $el->setComment(sb()
            ->t(
                'Allows you to choose how wide the %1$s interface should be.',
                $this->driver->getAppNameShort()
            )
            ->t('For example if you work on a smaller screen (laptop, tablet...), you can select the maximized width to use more of the available space.')
        );

        $widths = LayoutWidths::getInstance()->getAll();

        foreach($widths as $width) {
            $el->addOption($width->getLabel(), $width->getID());
        }
    }

    /**
     * @param HTML_QuickForm2_Container_Group $container
     * @return void
     */
    protected function injectUILocale(HTML_QuickForm2_Container_Group $container) : void
    {
        $el = Localization::injectAppLocalesSelector(self::SETTING_LOCALE, $container);
        $el->addClass('input-xlarge');
    }

    protected function injectDarkMode(HTML_QuickForm2_Container_Group $container) : void
    {
        $el = $this->addElementSwitch(self::SETTING_DARK_MODE, t('Dark mode?'), $container);
        $el->makeOnOff();
        $el->setComment(sb()
            ->t('Switches the UI colors to a dark variant.')
            ->nl()
            ->add(UI::label(t('BETA'))->makeWarning())
            ->t('Some elements have not been entirely optimized yet, so parts of the interface may have mixed light and dark colors.')
        );
    }
}