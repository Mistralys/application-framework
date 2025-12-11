<?php

declare(strict_types=1);

namespace Application\Maintenance\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\Maintenance\Admin\MaintenanceScreenRights;
use Application\Maintenance\Admin\Traits\MaintenanceSubmodeInterface;
use Application\Maintenance\Admin\Traits\MaintenanceSubmodeTrait;
use AppLocalize\Localization;
use DateInterval;
use DateTime;
use UI;
use UI_Form;
use UI_Themes_Theme_ContentRenderer;

class CreateSubmode extends BaseSubmode implements MaintenanceSubmodeInterface
{
    use MaintenanceSubmodeTrait;

    public const string URL_NAME = 'create';
    protected string $formName = 'maintenance_create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MaintenanceScreenRights::SCREEN_CREATE;
    }

    public function getNavigationTitle(): string
    {
        return t('Create plan');
    }

    public function getTitle(): string
    {
        return t('Create maintenance plan');
    }

    protected function _handleActions(): bool
    {
        $this->createSettingsForm();

        if ($this->isFormValid()) {
            $values = $this->getFormValues();

            $result = array();
            preg_match_all(self::REGEX_DATETIME, $values['datetime_start'], $result);

            $isoDate = $result[3][0] . $result[4][0] . '/' . $result[2][0] . '/' . $result[1][0] . ' ' . $result[5][0] . ':' . $result[6][0] . ':00';

            $start = new DateTime($isoDate);
            $end = new DateTime($isoDate);
            $interval = DateInterval::createFromDateString($values['duration']);
            $end->add($interval);

            $now = new DateTime();

            if ($end <= $now) {
                $this->ui->addErrorMessage(
                    UI::icon()->warning() . ' ' .
                    '<b>' . t('Invalid configuration:') . '</b> ' .
                    t('With these settings, the maintenance would end at %1$s, which is already past.', $end->format('d.m.Y H:i'))
                );
                return true;
            }

            $maintenance = AppFactory::createMaintenance();
            $plan = $maintenance->addPlan($start, $values['duration']);
            $locales = Localization::getAppLocales();
            foreach ($locales as $locale) {
                $plan->setInfoText($locale, $values['reasons_' . $locale->getName()]);
            }

            $maintenance->save();

            $message = t('The maintenance plan has been added successfully.');

            if ($plan->isEnabled()) {
                $message .= ' ' . t('Maintenance mode is now enabled.');
            }

            $this->redirectWithSuccessMessage(
                $message,
                $this->mode->getURL()
            );
        }

        return true;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithSidebar()
            ->appendFormable($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create_plan', t('Create now'))
            ->setIcon(UI::icon()->add())
            ->makePrimary()
            ->makeClickableSubmit($this->formableForm);

        $this->sidebar->addSeparator();

        $this->sidebar->addInfoMessage(
            t('Current server time:') . ' ' .
            date('H:i')
        );
    }

    public const string REGEX_DATETIME = '%(0?[1-9]|[12][0-9]|3[01])[- /.](0?[1-9]|1[012])[- /.](19|20)?([0-9]{2}) +([0-9]{1,2}):([0-9]{1,2})%';

    protected UI_Form $form;

    protected function createSettingsForm() : void
    {
        $defaultValues = array(
            'datetime_start' => date('d/m/Y H:i')
        );

        $this->createFormableForm($this->formName, $defaultValues);
        $this->addFormablePageVars();

        $this->addSection(t('Configuration'));

        $dateStart = $this->addElementText('datetime_start', t('Starting time'));
        $dateStart->addFilter('trim');
        $dateStart->setComment(
            t('Sets the date and time on which to start the maintenance.') . ' ' .
            t('Use the following format:') . ' ' .
            t('dd/mm/yyyy hh:mm') . ' ' .
            t('Note:') . ' ' .
            t('If you set this to a time in the past, the maintenance will start directly after creating the plan.')
        );
        $this->makeRequired($dateStart);

        $dateStart->addRule('regex', t('Invalid date string'), self::REGEX_DATETIME);

        $duration = $this->addElementText('duration', t('Downtime duration'));
        $duration->setAttribute('placeholder', '2 hours');
        $duration->addFilter('trim');
        $duration->setComment(
            t('Specify a duration in plain text (english only), e.g.:') . ' ' .
            '"10 minutes", "1 hour", "1 hour + 30 minutes" '
        );
        $duration->addRule('callback', t('Not a valid duration string.'), 'strtotime');
        $duration->addRule('callback', t('Duration must be a positive value.'), array($this, 'callback_validateDuration'));
        $this->makeRequired($duration);

        $locales = Localization::getAppLocales();
        foreach ($locales as $locale) {
            $reasons = $this->addElementTextarea('reasons_' . $locale->getName(), t('Information - %1$s', $locale->getLabel()));
            $reasons->addFilter('trim');
            $reasons->setComment(
                t('Optional information text regarding the maintenance:') . ' ' .
                t('Will be shown on the maintenance screen.') . ' ' .
                t('HTML can be used.')
            );
        }
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem(t('Maintenance plans'))->makeLinked($this->mode->getURL());
        $this->breadcrumb->appendItem(t('Add new plan'));
    }

    public function callback_validateDuration(mixed $value) : bool
    {
        if(empty($value) || !is_string($value)) {
            return true;
        }

        $now = new DateTime();
        $later = new DateTime();
        $interval = DateInterval::createFromDateString($value);
        $later->add($interval);

        return $later > $now;
    }
}