<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ListScreen;

use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\TimeEntry;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;
use AppUtils\ArrayDataCollection;
use UI;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseGlobalSettingsScreen extends Application_Admin_Area_Mode_Submode
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'time-settings';
    public const SETTING_BASE_TICKET_URL = 'base-ticket-url';
    public const FORM_NAME = 'time-settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Global Settings');
    }

    public function getTitle(): string
    {
        return t('Global Settings');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_GLOBAL_SETTINGS;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('save-settings', t('Save now'))
            ->setIcon(UI::icon()->save())
            ->makeClickableSubmit($this);
    }

    protected function _handleActions(): bool
    {
        $this->createSettingsForm();

        if($this->isFormValid()) {
            $this->handleSaveSettings(ArrayDataCollection::create($this->getFormValues()));
        }

        return true;
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->injectBaseTicketURL();
    }

    private function getDefaultFormValues() : array
    {
        return array(
            self::SETTING_BASE_TICKET_URL => TimeUIManager::getBaseTicketURL(),
        );
    }

    private function injectBaseTicketURL() : void
    {
        $this->addElementText(self::SETTING_BASE_TICKET_URL, t('Base ticket URL'))
            ->addClass(UI\CSSClasses::INPUT_XXLARGE)
            ->setComment(sb()
                ->t('This URL will be used as default to create links to tickets in the time tracker when no specific URL is specified.')
                ->nl()
                ->t('Use the placeholder %1$s to insert the ticket ID.', sb()->codeCopy(TimeEntry::PLACEHOLDER_TICKET_ID))
            );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function handleSaveSettings(ArrayDataCollection $formValues) : void
    {
        TimeUIManager::setBaseTicketURL($formValues->getString(self::SETTING_BASE_TICKET_URL));
    }
}
