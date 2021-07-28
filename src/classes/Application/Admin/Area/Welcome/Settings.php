<?php

declare(strict_types=1);

class Application_Admin_Area_Welcome_Settings extends Application_Admin_Area_Mode
{
    const URL_NAME_SETTINGS = 'settings';
    const FORM_NAME = 'welcome_settings';

    /**
     * @var Application_User_Recent
     */
    private $recent;

    /**
     * @var Application_User_Recent_Category[]
     */
    private $categories;

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
        return self::URL_NAME_SETTINGS;
    }

    public function getNavigationTitle()
    {
        return '';
    }

    public function getTitle()
    {
        return t('Quickstart settings');
    }

    protected function _handleActions()
    {
        $this->recent = $this->user->getRecent();
        $this->categories = $this->recent->getCategories();

        $this->createSettingsForm();

        if($this->isFormValid())
        {
            $this->handleSaveSettings($this->getFormValues());
        }
    }

    protected function _handleHelp()
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendItem(t('Quickstart'))->makeLinked($this->recent->getAdminURL());
        $this->breadcrumb->appendItem(t('Settings'))->makeLinked($this->recent->getAdminSettingsURL());
    }

    protected function _handleSidebar()
    {
        $this->sidebar->addButton('save_settings', t('Save now'))
            ->setIcon(UI::icon()->save())
            ->makePrimary()
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->recent->getAdminURL());
    }

    private function getDefaultFormValues() : array
    {
        $result = array();

        foreach($this->categories as $category)
        {
            $result[$this->getCategoryElementName($category)] = $category->getMaxItems();
        }

        return $result;
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->addElementHeaderII(t('Elements per category'))
            ->setAbstract(sb()
                ->t('This allows you to adjust how many items you wish to keep per category.')
                ->t('We recommend using the same value for all.')
                ->note()
                ->t('Setting the amount to %1$s effectively hides the category.', sb()->code('0'))

        );

        foreach($this->categories as $category)
        {
            $el = $this->addElementInteger(
                $this->getCategoryElementName($category),
                $category->getLabel(),
                null,
                0,
                60
            );
            $el->addClass('input-xsmall');
        }
    }

    private function getCategoryElementName(Application_User_Recent_Category $category) : string
    {
        return 'category_'.$category->getAlias();
    }

    private function handleSaveSettings(array $formValues) : void
    {
        $this->startTransaction();

        foreach($this->categories as $category)
        {
            $name = $this->getCategoryElementName($category);
            $value = intval($formValues[$name]);

            $category->setMaxItems($value);
        }

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t('The quickstart settings have been saved successfully at %1$s.', sb()->time()),
            $this->recent->getAdminURL()
        );
    }
}
