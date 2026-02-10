<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSpans\SpanTypes\TimeSpanTypes;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use UI;

class TimeSpanSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_DATE_START = 'dateStart';
    public const string SETTING_DATE_END = 'dateEnd';
    public const string SETTING_TYPE = 'type';
    public const string SETTING_COMMENTS = 'comments';
    public const string SETTING_DAYS = 'days';

    public function __construct(Application_Interfaces_Formable $formable, ?TimeSpanRecord $record = null)
    {
        parent::__construct($formable, AppFactory::createTimeTracker()->createTimeSpans(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings())
            ->expand();

        $group->registerSetting(self::SETTING_TYPE)
            ->makeRequired()
            ->setStorageName(TimeSpanRecord::COL_TYPE)
            ->setCallback(Closure::fromCallable(array($this, 'injectType')));

        $group->registerSetting(self::SETTING_DATE_START)
            ->makeRequired()
            ->setStorageName(TimeSpanRecord::COL_DATE_START)
            ->setCallback(Closure::fromCallable(array($this, 'injectDateStart')));

        $group->registerSetting(self::SETTING_DATE_END)
            ->makeRequired()
            ->setStorageName(TimeSpanRecord::COL_DATE_END)
            ->setCallback(Closure::fromCallable(array($this, 'injectDateEnd')));

        $group->registerSetting(self::SETTING_DAYS)
            ->makeRequired()
            ->setDefaultValue(1)
            ->setStorageName(TimeSpanRecord::COL_DAYS)
            ->setCallback(Closure::fromCallable(array($this, 'injectDays')));

        $group->registerSetting(self::SETTING_COMMENTS)
            ->setStorageName(TimeSpanRecord::COL_COMMENTS)
            ->setCallback(Closure::fromCallable(array($this, 'injectComments')));
    }

    private function injectDateStart(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return $this->addElementISODate($setting->getName(), t('Start date'));
    }

    private function injectDateEnd(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return $this->addElementISODate($setting->getName(), t('End date'));
    }

    private function injectType(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        return TimeSpanTypes::getInstance()
            ->createSelector($this)
            ->setName($setting->getName())
            ->inject();
    }

    private function injectDays(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementInteger($setting->getName(), t('Days'));
        $el->setComment(sb()
            ->t('The number of days this represents.')
            ->t('Remove any holidays or weekends from the count.')
        );

        return $el;
    }

    private function injectComments(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementTextarea($setting->getName(), t('Comments'));
        $el->setRows(2);
        $el->addFilterTrim();
        $el->setComment(t('Optional comments regarding the time span.'));

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_TYPE;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->can(TimeTrackerScreenRights::SCREEN_TIME_SPANS_EDIT);
    }
}
