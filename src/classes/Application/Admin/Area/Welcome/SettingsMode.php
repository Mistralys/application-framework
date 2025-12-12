<?php

declare(strict_types=1);

namespace Application\Admin\Area\Welcome;

use Application\Admin\Area\BaseMode;
use Application\Admin\Area\WelcomeArea;
use Application\Admin\ClassLoaderScreenInterface;
use Application_User;
use Application_User_Recent;
use Application_User_Recent_Category;
use UI;

class SettingsMode extends BaseMode implements ClassLoaderScreenInterface
{
    public const string URL_NAME_SETTINGS = 'settings';
    public const string FORM_NAME = 'welcome_settings';

    private Application_User_Recent $recent;

    /**
     * @var Application_User_Recent_Category[]
     */
    private array $categories;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getParentScreenClass(): string
    {
        return WelcomeArea::class;
    }

    public function getRequiredRight(): string
    {
        return Application_User::RIGHT_LOGIN;
    }

    public function getURLName(): string
    {
        return self::URL_NAME_SETTINGS;
    }

    public function getNavigationTitle(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return t('Quickstart settings');
    }

    protected function _handleActions(): bool
    {
        $this->recent = $this->user->getRecent();
        $this->categories = $this->recent->getCategories();

        $this->createSettingsForm();

        if ($this->isFormValid()) {
            $this->handleSaveSettings($this->getFormValues());
        }

        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem(t('Quickstart'))->makeLinked($this->recent->getAdminURL());
        $this->breadcrumb->appendItem(t('Settings'))->makeLinked($this->recent->getAdminSettingsURL());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('save_settings', t('Save now'))
            ->setIcon(UI::icon()->save())
            ->makePrimary()
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->recent->getAdminURL());
    }

    private function getDefaultFormValues(): array
    {
        $result = array();

        foreach ($this->categories as $category) {
            $result[$this->getCategoryElementName($category)] = $category->getMaxItems();
        }

        return $result;
    }

    private function createSettingsForm(): void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->addElementHeaderII(t('Elements per category'))
            ->setIcon(UI::icon()->list())
            ->setAbstract(sb()
                ->t('This allows you to adjust how many items you wish to keep per category.')
                ->t('We recommend using the same value for all.')
                ->note()
                ->t('Setting the amount to %1$s effectively hides the category.', sb()->code('0'))

            );

        foreach ($this->categories as $category) {
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

    private function getCategoryElementName(Application_User_Recent_Category $category): string
    {
        return 'category_' . $category->getAlias();
    }

    private function handleSaveSettings(array $formValues): void
    {
        $this->startTransaction();

        foreach ($this->categories as $category) {
            $name = $this->getCategoryElementName($category);
            $value = (int)$formValues[$name];

            $category->setMaxItems($value);
        }

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t('The quickstart settings have been saved successfully at %1$s.', sb()->time()),
            $this->recent->getAdminURL()
        );
    }
}
