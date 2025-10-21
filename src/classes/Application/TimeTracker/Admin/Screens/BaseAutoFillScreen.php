<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode;
use AppUtils\ArrayDataCollection;
use AppUtils\Microtime;
use DateInterval;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeTrackerCollection;
use UI;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseAutoFillScreen extends Application_Admin_Area_Mode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'auto-fill';
    public const string FORM_NAME = 'auto-fill-times';
    public const int VARIATION_PERCENT_MIN = 20;
    public const int VARIATION_PERCENT_MAX = 100;
    public const string SETTING_START_TIME_VARIATION = 'start_time_variation';
    public const string SETTING_EARLIEST_TIME = 'earliest_time';
    public const string SETTING_WORK_HOURS = 'work_hours_per_day';
    public const string SETTING_OVERTIME_BIAS = 'overtime_bias';
    public const string SETTING_MAX_OVERTIME = 'max_overtime';
    public const string SETTING_MAX_UNDERTIME = 'max_undertime';
    public const string SETTING_DATE = 'date';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Auto-fill time entries');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createTimeTracker()->adminURL()->list());
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_CREATE_ENTRY;
    }

    public function getNavigationTitle(): string
    {
        return t('Auto-fill');
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->createSettingsForm();

        if($this->isFormValid()) {
            $this->handleCalculate();
        }

        return true;
    }

    private function handleCalculate() : void
    {
        $values = ArrayDataCollection::create($this->getFormValues());

        $date = $values->requireMicrotime(self::SETTING_DATE);

        $dayStartTime = $this->calculateStartTime($values);

        $dayWorkHours = $this->getBiasedWorkHours(
            baseHours: $values->getFloat(self::SETTING_WORK_HOURS),
            minDelta: -$values->getFloat(self::SETTING_MAX_UNDERTIME),
            maxDelta: $values->getFloat(self::SETTING_MAX_OVERTIME),
            bias: $values->getFloat(self::SETTING_OVERTIME_BIAS)
        );

        $entries = AppFactory::createTimeTracker()
            ->getFilterCriteria()
            ->setFixedDate($date)
            ->getItemsObjects();

        $workBlocks = array();

        foreach($entries as $entry) {
            $entryStartTime = $entry->getStartTime();
            $entryEndTime = $entry->getEndTime();
        }
    }

    private function getBiasedWorkHours(float $baseHours = 8.0, float $minDelta = -2.0, float $maxDelta = 4.0, float $bias = 2.0): float
    {
        // $bias > 1: more likely to get higher hours; $bias < 1: more likely to get lower hours
        $rand = mt_rand() / mt_getrandmax();
        $biased = $rand ** (1 / $bias); // Skew towards higher values if bias > 1
        $delta = $minDelta + ($maxDelta - $minDelta) * $biased;
        return round($baseHours + $delta, 2);
    }

    private function calculateStartTime(ArrayDataCollection $values) : Microtime
    {
        $earliestTime = $values->requireMicrotime(self::SETTING_EARLIEST_TIME);
        $variationMinutes = $values->getInt(self::SETTING_START_TIME_VARIATION);
        $variationPercent = random_int(0, 100) / 100.0;
        $variationOffset = (int)round($variationMinutes * $variationPercent);

        // round offset up or down to within 5 minutes
        $variationOffset = (int)(5 * round($variationOffset / 5.0));

        return Microtime::createFromDate($earliestTime->add(DateInterval::createFromDateString("{$variationOffset} minutes")));
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->wizard());
    }

    protected function _handleSidebar(): void
    {
        if(!$this->isFormSubmitted()) {
            $this->sidebar->addButton('roll', t('Calculate'))
                ->setIcon(UI::icon()->calculate())
                ->makePrimary()
                ->makeClickableSubmit($this);
            return;
        }


        $this->sidebar->addButton('create_entries', t('Create entries'))
            ->makePrimary()
            ->setIcon(UI::icon()->add())
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('re_roll', t('Re-roll calculations'))
            ->setIcon(UI::icon()->calculate())
            ->makeClickableSubmit($this);
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function getDefaultFormValues() : array
    {
        return array(
            self::SETTING_EARLIEST_TIME => '07:00',
            self::SETTING_START_TIME_VARIATION => '60',
            self::SETTING_WORK_HOURS => '8.0',
            self::SETTING_MAX_OVERTIME => '4.0',
            self::SETTING_MAX_UNDERTIME => '2.0',
            self::SETTING_OVERTIME_BIAS => '1.0',
            self::SETTING_DATE => TimeUIManager::getLastUsedDate()->format('Y-m-d')
        );
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->addSection(t('Generation details'))
            ->setIcon(UI::icon()->information());

        $this->injectDate();

        $this->addSection(t('Auto-fill Settings'))
            ->setIcon(UI::icon()->settings())
            ->collapse();

        $this->injectEarliestTime();
        $this->injectStartTimeVariation();
        $this->injectWorkHours();
        $this->injectMaxOvertime();
        $this->injectMaxUndertime();
        $this->injectOvertimeBias();
    }

    private function injectDate() : void
    {
        $el = $this->addElementISODate(self::SETTING_DATE, t('Date'));

        $this->makeRequired($el);
    }

    private function injectEarliestTime() : void
    {
        $this->addElementText(self::SETTING_EARLIEST_TIME, t('Earliest time'))
            ->setComment(t('Entries will be auto-filled starting from this time each day. Format: HH:MM'));
    }

    private function injectStartTimeVariation() : void
    {
        $el = $this->addElementText(self::SETTING_START_TIME_VARIATION, t('Start time variation'))
            ->addClass(UI\CSSClasses::INPUT_XSMALL)
            ->setComment(t(
                'A random percentage (between %1$s to %2$s) of this variation will be applied to the starting time of each entry.',
                self::VARIATION_PERCENT_MIN,
                self::VARIATION_PERCENT_MAX
            ));

        $this->setElementAppend($el, t('Minutes'));
    }

    private function injectWorkHours() : void
    {
        $this->addElementText(self::SETTING_WORK_HOURS,t('Work hours per day'));
    }

    private function injectMaxOvertime() : void
    {
        $this->addElementText(self::SETTING_MAX_OVERTIME, t('Maximum overtime'));
    }

    private function injectMaxUndertime() : void
    {
        $this->addElementText(self::SETTING_MAX_UNDERTIME, t('Maximum undertime'));
    }

    private function injectOvertimeBias() : void
    {
        $el = $this->addElementText(self::SETTING_OVERTIME_BIAS, t('Overtime bias'));
        $el->setComment(sb()
            ->t('How biased the calculation is towards generating overtime.')
            ->t('Higher than 1: More likely to get higher hours; Lesser than 1: More likely to get lower hours.')
        );
    }


}
