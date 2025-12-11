<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\AppSetSubmodeInterface;
use Application\Sets\Admin\Traits\AppSetSubmodeTrait;
use Application_Admin_Area;
use Application_Sets;
use Application_Sets_Set;
use UI;
use UI_Themes_Theme_ContentRenderer;

class CreateSetSubmode extends BaseSubmode implements AppSetSubmodeInterface
{
    use AppSetSubmodeTrait;

    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_APP_SETS_CREATE;
    }

    public function getTitle(): string
    {
        return t('Create a new application set');
    }

    public function getNavigationTitle(): string
    {
        return t('Create new set');
    }

    protected Application_Sets $sets;

    /**
     * @var Application_Admin_Area[]
     */
    protected array $areas;

    protected function _handleActions(): bool
    {
        $this->sets = AppFactory::createAppSets();
        $this->areas = $this->driver->getAdminAreaObjects();

        $this->createSettingsForm();

        if (!$this->isFormValid()) {
            return true;
        }

        $set = Application_Sets_Set::createFromFormable($this);
        $this->sets->save();

        $this->redirectWithSuccessMessage(
            t(
                'The application set %1$s was created successfully at %2$s.',
                $set->getID(),
                date('H:i:s')
            ),
            $this->sets->getAdminListURL()
        );
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('addset', t('Add new set'))
            ->setIcon(UI::icon()->add())
            ->makePrimary()
            ->makeClickable(sprintf("application.submitForm('%s')", $this->formableForm->getName()));

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->sets->getAdminListURL());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromSubmode($this);
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithSidebar()
            ->appendFormable($this);
    }

    protected string $formName = 'appsets';

    protected function createSettingsForm(): void
    {
        Application_Sets_Set::createSettingsForm($this);

        $this->addFormablePageVars();
    }
}
