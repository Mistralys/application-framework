<?php

declare(strict_types=1);

/**
 * @see template_default_content_welcome
 */
class Application_Admin_Area_Welcome extends Application_Admin_Area
{
    public function getDefaultMode()
    {
        return '';
    }

    public function getNavigationGroup()
    {
        return '';
    }

    public function isUserAllowed()
    {
        return true;
    }

    public function getDependencies()
    {
        return array();
    }

    public function isCore()
    {
        return false;
    }

    public function getURLName()
    {
        return 'welcome';
    }

    public function getNavigationTitle()
    {
        return '';
    }

    public function getTitle()
    {
        return t('Quickstart');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->home();
    }

    protected function _handleHelp()
    {
        $this->renderer->setTitle($this->getTitle())->getTitle()->setIcon($this->getNavigationIcon());
    }

    public function _renderContent()
    {
        $tpl = $this->ui->createTemplate('content/welcome')
            ->setVar('user', $this->user)
            ->setVar('recent', $this->user->getRecent());

        return $this->renderer
            ->appendTemplate($tpl)
            ->makeWithSidebar();
    }
}
