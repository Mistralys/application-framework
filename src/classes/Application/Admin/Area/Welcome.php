<?php

declare(strict_types=1);

/**
 * @see template_default_content_welcome
 */
class Application_Admin_Area_Welcome extends Application_Admin_Area
{
    const URL_NAME_WELCOME = 'welcome';

    /**
     * @var Application_User_Recent
     */
    private $recent;

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
        return self::URL_NAME_WELCOME;
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

    protected function _handleActions()
    {
        $this->recent = $this->user->getRecent();

        if($this->request->hasParam('clear-category'))
        {
            $this->handleClearCategory(strval($this->request->getParam('clear-category')));
        }
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
            ->makeWithoutSidebar();
    }

    private function handleClearCategory(string $categoryAlias) : void
    {
        $category = $this->recent->getCategoryByAlias($categoryAlias);
        $category->clearEntries();

        $this->redirectWithSuccessMessage(
            t('The %1$s history has been cleared successfully.', $category->getLabel()),
            $this->recent->getAdminURL()
        );
    }
}
