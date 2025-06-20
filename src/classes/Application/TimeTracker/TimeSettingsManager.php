<?php

declare(strict_types=1);

namespace Application\TimeTracker;

use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\TimeTracker\Admin\TimeUIManager;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\DateTimeHelper\TimeDurationCalculator;
use AppUtils\Microtime;
use Closure;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Node;
use Application\TimeTracker\Types\TimeEntryTypes;
use HTML_QuickForm2_Rule_Callback;

class TimeSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const SETTING_DATE = 'date';
    public const SETTING_START_TIME = 'start';
    public const SETTING_END_TIME = 'end';
    public const SETTING_TYPE = 'type';
    public const SETTING_DURATION = 'duration';
    public const SETTING_TICKET = 'ticket';
    public const SETTING_COMMENTS = 'comments';
    public const FORMAT_PLACEHOLDER = '$format';
    public const SETTING_TICKET_URL = 'ticketURL';

    public function __construct(Application_Formable $formable, ?TimeEntry $record = null)
    {
        parent::__construct($formable, AppFactory::createTimeTracker(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        $recordData->setKey(TimeTrackerCollection::COL_USER_ID, $this->getUser()->getID());
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Time and duration'))
            ->expand()
            ->setAbstract((string)sb()
                ->t('This is calculated intelligently:')
                ->bold(t('At minimum, enter a duration.'))
                ->t(
                    'Otherwise, %1$sany combination of two values%2$s will automatically fill out the remaining values.',
                    '<b>',
                    '</b>'
                )
            );

        $group->registerSetting(self::SETTING_START_TIME)
            ->setStorageName(TimeTrackerCollection::COL_TIME_START)
            ->setStorageFilter(Closure::fromCallable(array($this, 'calculateStartTime')))
            ->setImportFilter(Closure::fromCallable(array($this, 'filterTimeDefault')))
            ->setCallback(Closure::fromCallable(array($this, 'injectStartTime')));

        $group->registerSetting(self::SETTING_END_TIME)
            ->setStorageName(TimeTrackerCollection::COL_TIME_END)
            ->setStorageFilter(Closure::fromCallable(array($this, 'calculateEndTime')))
            ->setImportFilter(Closure::fromCallable(array($this, 'filterTimeDefault')))
            ->setCallback(Closure::fromCallable(array($this, 'injectEndTime')));

        $group->registerSetting(self::SETTING_DURATION)
            ->setStorageName(TimeTrackerCollection::COL_DURATION)
            ->setStorageFilter(Closure::fromCallable(array($this, 'calculateDuration')))
            ->setImportFilter(Closure::fromCallable(array($this, 'filterDurationDefault')))
            ->setCallback(Closure::fromCallable(array($this, 'injectDuration')));

        $group = $this->addGroup(t('Settings'))
            ->expand();

        $group->registerSetting(self::SETTING_TYPE)
            ->makeRequired()
            ->setStorageName(TimeTrackerCollection::COL_TYPE)
            ->setDefaultValue(TimeEntryTypes::DEFAULT_TYPE)
            ->setCallback(Closure::fromCallable(array($this, 'injectType')));

        $group->registerSetting(self::SETTING_TICKET)
            ->setStorageName(TimeTrackerCollection::COL_TICKET)
            ->setCallback(Closure::fromCallable(array($this, 'injectTicket')));

        $group->registerSetting(self::SETTING_TICKET_URL)
            ->setStorageName(TimeTrackerCollection::COL_TICKET_URL)
            ->setCallback(Closure::fromCallable(array($this, 'injectTicketURL')));

        $group->registerSetting(self::SETTING_COMMENTS)
            ->setStorageName(TimeTrackerCollection::COL_COMMENTS)
            ->setCallback(Closure::fromCallable(array($this, 'injectComments')));

        $group->registerSetting(self::SETTING_DATE)
            ->makeRequired()
            ->setStorageName(TimeTrackerCollection::COL_DATE)
            ->setDefaultValue(TimeUIManager::getLastUsedDate()->format('Y-m-d'))
            ->setCallback(Closure::fromCallable(array($this, 'injectDate')));
    }

    private function filterTimeDefault($value) : string
    {
        return DaytimeStringInfo::fromString((string)$value)->getNormalized();
    }

    /**
     * Converts the seconds stored in the DB to the expected duration string.
     * @param string|int|null $value
     * @return string
     */
    private function filterDurationDefault($value) : string
    {
        if(is_numeric($value)) {
            return DurationStringInfo::fromSeconds((int)$value)->getNormalized();
        }

        return DurationStringInfo::fromString($value)->getNormalized();
    }

    private function calculateDuration($value, Application_Formable_RecordSettings_ValueSet $values) : string
    {
        return (string)$this->getCalculation($values)->getDuration()->getTotalSeconds();
    }

    private function getCalculation(Application_Formable_RecordSettings_ValueSet $values) : TimeDurationCalculator
    {
        $calc = TimeDurationCalculator::create(
            (string)$values->getKey(self::SETTING_START_TIME),
            (string)$values->getKey(self::SETTING_END_TIME),
            (string)$values->getKey(self::SETTING_DURATION)
        );

        if($calc->isValid()) {
            return $calc;
        }

        throw new TimeTrackerException(
            'Invalid time entry data submitted.',
            $calc->getErrorMessage(),
            TimeTrackerException::ERROR_INVALID_DURATION_DATA_SUBMITTED
        );
    }

    private function calculateStartTime($value, Application_Formable_RecordSettings_ValueSet $values) : ?string
    {
        return $this->resolveTimeValue($this->getCalculation($values)->getStartTime());
    }

    private function calculateEndTime($value, Application_Formable_RecordSettings_ValueSet $values) : ?string
    {
        return $this->resolveTimeValue($this->getCalculation($values)->getEndTime());
    }

    private function resolveTimeValue(?DaytimeStringInfo $time) : ?string
    {
        if($time !== null && !$time->isEmpty()) {
            return $time->getNormalized();
        }

        return null;
    }

    private function injectDate(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return $this->addElementISODate($setting->getName(), t('Date'));
    }

    private function injectStartTime(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return $this->createTimeElement(
            $setting,
            t('Start time'),
            t('Enter the time when the task started in the format %1$s.', self::FORMAT_PLACEHOLDER)
        );
    }

    private function injectEndTime(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return $this->createTimeElement(
            $setting,
            t('End time'),
            t('Enter the time when the task ended in the format %1$s.', self::FORMAT_PLACEHOLDER)
        );
    }

    private function createTimeElement(Application_Formable_RecordSettings_Setting $setting, string $label, string $description) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), $label);
        $el->addFilterTrim();
        $el->addClass('input-small');
        $el->setComment(sb()
            ->add(str_replace(self::FORMAT_PLACEHOLDER, (string)sb()->code('14:30'), $description))
            ->nl()
            ->t('For ease of typing, you can use the following divider characters:')
            ->add('<code>'.implode('</code><code>', DaytimeStringInfo::ALLOWED_SEPARATOR_CHARS).'</code>')
        );

        return $el;
    }

    private function injectDuration(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Duration'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $el->setComment(sb()
            ->t('Enter a duration in the format %1$s.', sb()->code('1d 3h 45m'))
            ->nl()
            ->t('You may use any combination, order and number of days, hours and minutes.')
            ->nl()
            ->t(
                'Multiple values are added together, so %1$s will be merged to %2$s, for example.',
                sb()->code('30m 10m 5m'),
                sb()->code('45m')
            )
            ->nl()
            ->t('You can use the following labels in any mix you prefer:')
            ->ul(array(
                sb()->code('d')->add('/')->code('day')->add('/')->code('days'),
                sb()->code('h')->add('/')->code('hour')->add('/')->code('hours'),
                sb()->code('m')->add('/')->code('minute')->add('/')->code('minutes')
            ))
        );

        $this->addRuleCallback($el, Closure::fromCallable(array($this, 'validateDuration')), '');

        return $el;
    }

    private function validateDuration($value, HTML_QuickForm2_Rule_Callback $rule) : bool
    {
        $calc = TimeDurationCalculator::create(
            (string)$this->requireElementByName(self::SETTING_START_TIME)->getValue(),
            (string)$this->requireElementByName(self::SETTING_END_TIME)->getValue(),
            (string)$value
        );

        if($calc->isValid()) {
            return true;
        }

        $rule->setMessage((string)$calc->getErrorMessage());
        return false;
    }

    private function injectType(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementSelect($setting->getName(), t('Type'));
        $el->addClass('input-xlarge');

        foreach(TimeEntryTypes::getInstance()->getAll() as $type) {
            $el->addOption($type->getLabel(), $type->getID());
        }

        $el->setSize($el->countOptions());

        return $el;
    }

    private function injectTicket(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Ticket'));
        $el->addFilterTrim();
        $el->addClass('input-xxlarge');
        $el->setComment(t('The related ticket number, if any.'));

        return $el;
    }

    private function injectTicketURL(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Ticket link'));
        $el->addFilterTrim();
        $el->addClass('input-xxlarge');
        $el->setComment(t('Link to the ticket in the ticketing system, if relevant.'));

        return $el;
    }

    private function injectComments(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementTextarea($setting->getName(), t('Comments'));
        $el->setRows(2);
        $el->addFilterTrim();
        $el->setComment(t('Optional comments regarding the task.'));

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_START_TIME;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->isDeveloper();
    }
}
