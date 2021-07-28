<?php

declare(strict_types=1);

/**
 * @see template_default_content_welcome
 */
class Application_Admin_Area_Welcome_Overview extends Application_Admin_Area_Mode
{
    const URL_NAME_OVERVIEW = 'overview';

    /**
     * @var Application_User_Recent
     */
    private $recent;

    public function getDefaultSubmode()
    {
        return '';
    }

    public function isUserAllowed()
    {
        return true;
    }

    public function getURLName()
    {
        return self::URL_NAME_OVERVIEW;
    }

    public function getNavigationTitle()
    {
        return t('Quickstart');
    }

    public function getTitle()
    {
        return t('Quickstart');
    }

    protected function _handleActions()
    {
        $this->recent = $this->user->getRecent();

        if($this->request->hasParam('clear-category'))
        {
            $this->handleClearCategory(strval($this->request->getParam('clear-category')));
        }
    }

    private function getCategoryLabels() : array
    {
        $categories = $this->recent->getCategories();
        $items = array();
        foreach ($categories as $category)
        {
            $items[] = $category->getLabel();
        }

        return $items;
    }

    protected function _handleHelp()
    {
        $this->help->setSummary(t('Your personal activity tracker'));

        $this->help->addPara(sb()
            ->t('The tracker will add elements you visit in %1$s to your quickstart, so you can easily find them again.', $this->driver->getAppNameShort())
            ->t('This includes the following categories:')
        );

        $this->help->addPara(sb()
            ->ul($this->getCategoryLabels())
        );

        $this->help->addPara(sb()
            ->t('Every time you visit the same element again, it is moved up to the top of the list.')
            ->t('This way, you can always see what you worked on last.')
            ->t(
                'By default, up to %1$s elements are shown in each category (you can customize this in the settings).',
                $this->recent->getMaxItemsDefault()
            )
            ->t('The oldest elements are dropped off the end of the list when the maximum amount is reached.')
        );

        $this->help->addHeader(t('Turning off the quickstart'));

        $this->help->addPara(sb()
            ->t('The quickstart can not be turned off entirely.')
            ->t('However, you can change the page you see when you log in.')
            ->t(
                'For this, go into your %1$suser settings%2$s, and select the startup tab you would prefer.',
                '<a href="'.$this->user->getAdminSettingsURL().'">',
                '</a>'
            )
            ->t('The quickstart will still be available should you need it later.')
        );

        $this->renderer->getTitle()
            ->setIcon($this->area->getNavigationIcon())
            ->addContextElement(
                UI::button(t('Open notepad'))
                    ->setIcon(UI::icon()->notepad())
                    ->setTooltip(Application_User_Notepad::getTooltipText())
                    ->click(Application_User_Notepad::getJSOpen())
            )
            ->addContextElement(
                UI::button(t('Settings'))
                    ->setIcon(UI::icon()->settings())
                    ->setTooltip(t('Open the quickstart settings.'))
                    ->link($this->recent->getAdminSettingsURL())
            );

        $this->renderer->setTitle(sb()
            ->add($this->getTitle())
            ->add(UI::label(t('BETA'))->makeWarning())
        );
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
